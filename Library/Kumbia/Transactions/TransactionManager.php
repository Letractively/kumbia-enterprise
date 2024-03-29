<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Transactions
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * @see TransactionManagerInterface
 */
require 'Library/Kumbia/Transactions/Interface.php';

/**
 * TransactionManager
 *
 * Administra las Transacciones Globales en la Aplicacion
 *
 * @category	Kumbia
 * @package 	Transactions
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class TransactionManager
#if[compile-time]
	implements TransactionManagerInterface
#endif
	{

	/**
	 * Lista en la que se administran las transacciones
	 *
	 * @staticvar
	 * @var array
	 */
	static private $_transactions = array();

	/**
	 * Puntero a asignar a nuevas transacciones
	 *
	 * @var int
	 */
	static private $_dependencyPointer = 0x00;

	/**
	 * Devuelve la última creada ó crea una transacción
	 *
	 * @access	public
	 * @param  	TransactionDefinition $definition
	 * @return 	ActiveRecordTransaction
	 * @static
	 */
	public static function getUserTransaction($definition=''){
		if($definition!==''){
			if($definition instanceof TransactionDefinition){
				$transaction = new ActiveRecordTransaction(false, $definition);
				$transaction->setTransactionManager('TransactionManager');
				return $transaction;
			} else {
				throw new TransactionManagerException('El TransactionDefinition es inválido');
			}
		}
		if(count(self::$_transactions)==0){
			$transaction = new ActiveRecordTransaction(true);
			$transaction->setTransactionManager('TransactionManager');
			$transaction->setDependencyPointer(self::$_dependencyPointer);
			self::$_dependencyPointer+=2048;
			self::$_transactions[] = $transaction;
		} else {
			$transaction = self::$_transactions[count(self::$_transactions)-1];
			$transaction->setIsNewTransaction(false);
		}
		return $transaction;
	}

	/**
	 * Inicializa el TransactionManager
	 *
	 * @access public
	 * @static
	 */
	public static function initializeManager(){
		register_shutdown_function(array('TransactionManager', 'rollbackPendent'));
	}

	/**
	 * Cancela las transacciones pendientes
	 *
	 * @access public
	 * @static
	 */
	public static function rollbackPendent(){
		try {
			self::rollback();
		}
		catch(Exception $e){
			echo get_class($e).': '.$e->getMessage();
		}
	}

	/**
	 * Realiza commit a todas las transacciones del TransactionManager
	 *
	 * @access public
	 * @static
	 */
	public static function commit(){
		foreach(self::$_transactions as $transaction){
			$connection = $transaction->getConnection();
			if($connection->isUnderTransaction()==true){
				$connection->commit();
			}
		}
	}

	/**
	 * Realiza commit a todas las transacciones del TransactionManager
	 *
	 * @access	public
	 * @param 	boolean $collect
	 * @static
	 */
	public static function rollback($collect=false){
		foreach(self::$_transactions as $transaction){
			$connection = $transaction->getConnection();
			if($connection->isUnderTransaction()==true){
				$connection->rollback();
				$connection->close();
			}
			if($collect==true){
				self::_collectTransaction($transaction);
			}
		}
	}

	/**
	 * Notifica el rollback de una transacción administrada
	 *
	 * @param ActiveRecordTransaction $transaction
	 */
	public static function notifyRollback($transaction){
		foreach(EntityManager::getAllCreatedGenerators() as $generator){
			$generator->finalizeConsecutive();
		}
		self::_collectTransaction($transaction);
	}

	/**
	 * Notifica el commit de una transacción administrada
	 *
	 * @param ActiveRecordTransaction $transaction
	 */
	public static function notifyCommit($transaction){
		foreach(EntityManager::getAllCreatedGenerators() as $generator){
			$generator->finalizeConsecutive();
		}
		self::_collectTransaction($transaction);
	}

	/**
	 * Destruye la transacción activa del TransactionManager
	 *
	 * @param ActiveRecordTransaction $transaction
	 */
	private static function _collectTransaction($transaction){
		if(count(self::$_transactions)>0){
			$number = 0;
			foreach(self::$_transactions as $managedTransaction){
				if($managedTransaction==$transaction){
					unset(self::$_transactions[$number]);
					unset($transaction);
				}
				$number++;
			}
			$transactions = array();
			foreach(self::$_transactions as $managedTransaction){
				$transactions[] = $managedTransaction;
			}
			self::$_transactions = $transactions;
		}
	}

	/**
	 * Destruye todas las transacciones activas
	 *
	 * @static
	 */
	public static function collectTransactions(){
		if(count(self::$_transactions)>0){
			$number = 0;
			foreach(self::$_transactions as $managedTransaction){
				unset(self::$_transactions[$number]);
				unset($transaction);
				$number++;
			}
		}
	}

}
