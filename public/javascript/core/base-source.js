
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
 * @package 	Tag
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

var Base = {

	PROTOTYPE: 1,
	JQUERY: 2,
	EXT: 3,
	MOOTOOLS: 4,

	framework: 0,

	bind: function(){
        var _func = arguments[0] || null;
        var _obj = arguments[1] || this;
        var i = 0;
        var _args = [];
        for(var i=0;i<arguments.length;i++){
        	if(i>1){
        		_args[_args.length] = arguments[i];
        	};
        	i++;
        };
        return function(){
			return _func.apply(_obj, _args);
        };
	},

	_checkFramework: function(){
		if(typeof Prototype != "undefined"){
			Base.activeFramework = Base.PROTOTYPE;
			return;
		};
		if(typeof jQuery != "undefined") {
			Base.activeFramework = Base.JQUERY;
			return;
		};
		if(typeof Ext != "undefined"){
			Base.activeFramework = Base.EXT;
			return;
		};
		if(typeof MooTools != "undefined"){
			Base.activeFramework = Base.MOOTOOLS;
			return;
		};
		return 0;
	},

	$: function(element){
		return document.getElementById(element);
	},

	show: function(element){
		document.getElementById(element).style.display = "";
	},

	hide: function(element){
		document.getElementById(element).style.display = "none";
	},

	setValue: function(element, value){
		document.getElementById(element).value = value;
	},

	getValue: function(element){
		element = document.getElementById(element);
		if(element.tagName=='SELECT'){
			return element.options[element.selectedIndex].value;
		} else {
			return element.value;
		}
	}
};

var NumericField = {

	maskNum: function(evt){
		evt = (evt) ? evt : ((window.event) ? window.event : null);
		var kc = evt.keyCode;
		var ev = (evt.altKey==false)&&(evt.shiftKey==false)&&((kc>=48&&kc<=57)||(kc>=96&&kc<=105)||(kc==8)||(kc==9)||(kc==13)||(kc==17)||(kc==36)||(kc==35)||(kc==37)||(kc==46)||(kc==39)||(kc==190));
		if(!ev){
			evt.preventDefault();
    		evt.stopPropagation();
    		evt.stopped = true;
		}
	}

};

var DateField = {

	refresh: function(name){

		var monthTable = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var year = Base.getValue(name+'Year');
		var month = Base.getValue(name+'Month');
		var day = Base.getValue(name+'Day');
		var daySelect = Base.$(name+'Day');
		var html = '', n, numberDays;

		Base.setValue(name, year+'-'+month+'-'+day)

		while(daySelect.lastChild){
			daySelect.removeChild(daySelect.lastChild);
		};

		month = parseInt(month);
		numberDays = monthTable[month-1];
		if(month==2){
			if(parseInt(year)%4==0){
				numberDays = 29;
			}
		};
		for(var i=1;i<=numberDays;++i){
			n = (i < 10) ? '0'+i : i;
			if(n==day){
				html+='<option value="'+n+'" selected="selected">'+n+'</option>';
			} else {
				html+='<option value="'+n+'">'+n+'</option>';
			}
		}
		daySelect.innerHTML = html;
	}

};

var Utils = {

	getKumbiaURL: function(url){
		if(typeof url == "undefined"){
			url = "";
		};
		if($Kumbia.app!=""){
			return $Kumbia.path+$Kumbia.app+"/"+url;
		} else {
			return $Kumbia.path+url;
		}
	},

	redirectParentToAction: function(url){
		new Utils.redirectToAction(url, window.parent);
	},

	redirectOpenerToAction: function(url){
		new Utils.redirectToAction(url, window.opener);
	},

	redirectToAction: function(url, win){
		win = win ? win : window;
		win.location = Utils.getKumbiaURL() + url;
	}
};

function ajaxRemoteForm(form, up, callback){
	if(callback==undefined){
		callback = {};
	};
	new Ajax.Updater(up, form.action, {
		 method: "post",
		 asynchronous: true,
         evalScripts: true,
         onSuccess: function(transport){
			$(up).update(transport.responseText)
		},
		onLoaded: callback.before!=undefined ? callback.before: function(){},
		onComplete: callback.success!=undefined ? callback.success: function(){},
  		parameters: Form.serialize(form)
    });
  	return false;
};

var AJAX = {

	doRequest: function(url, options){
		var framework = Base.activeFramework;
		if(typeof options == 'undefined'){
			options = {};
		};
		switch(framework){
			case Base.PROTOTYPE:
				var callbackMap = {
					'before': 'onLoading',
					'success': 'onSuccess',
					'complete': 'onComplete',
					'error': 'onFailure'
				};
				$H(callbackMap).each(function(callback){
					if(typeof options[callback[0]] != 'undefined'){
						options[callback[1]] = function(procedure, transport){
							procedure.bind(this, transport.responseText)();
						}.bind(this, options[callback[0]]);
					}
				});
				return new Ajax.Request(url, options);
			case Base.JQUERY:
				var paramMap = {
					'method': 'type',
					'parameters': 'data',
					'asynchronous': 'async'
				};
				$.each(paramMap, function(index, value){
					if(typeof options[index] != 'undefined'){
						options[value] = options[index];
					}
				});
				options.url = url;
				return $.ajax(options);
			case Base.EXT:
				var paramMap = {
					'before': 'beforerequest',
					'error': 'failure',
					'parameters': 'params'
				};
				var index;
				for(index in paramMap){
					if(typeof options[index] != 'undefined'){
						options[paramMap[index]] = options[index];
					}
				};
				options.url = url;
				return Ext.Ajax.request(options);
			case Base.MOOTOOLS:
				var paramMap = {
					'parameters': 'data',
					'asynchronous': 'async',
					'before': 'onRequest',
					'success': 'onSuccess',
					'error': 'onFailure',
					'complete': 'onComplete'
				};
				var index;
				for(index in paramMap){
					if(typeof options[index] != 'undefined'){
						options[paramMap[index]] = options[index];
					}
				};
				options.url = url;
				var request = new Request(options);
				request.send();
				return request;
			break;
		};
	},

	update: function(url, element, options){
		if(typeof options == 'undefined'){
			options = {};
		};
		options.success = function(responseText){
			Base.$(element).innerHTML = responseText;
		};
		Base.bind(options.success, element, element);
		return AJAX.doRequest(url, options);
	}

};

AJAX.xmlRequest = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	return AJAX.doRequest(options.url, options)
};

AJAX.viewRequest = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	container = params.container;
	options.evalScripts = true;
	if(!document.getElementById(container)){
		throw "CoreError: DOM Container '"+container+"' no encontrado";
		return null
	};
	return AJAX.update(container, url, options);
};

AJAX.execute = function(params){
	var options = {};
	if(typeof params.url == "undefined" && typeof params.action != "undefined"){
		options.url = Utils.getKumbiaURL(params.action);
	};
	return AJAX.doRequest(options.url, options)
}

AJAX.query = function(queryAction){
	var me;
	new Ajax.Request(Utils.getKumbiaURL(queryAction), {
		method: 'GET',
		asynchronous: false,
		onSuccess: function(transport){
			var xml = transport.responseXML;
			var data = xml.getElementsByTagName("data");
			if(Prototype.Browser.IE){
				xmlValue = data[0].text;
			} else {
				xmlValue = data[0].textContent;
			};
			me = xmlValue;
		}
	});
	return me;
}

if(document.addEventListener){
	document.addEventListener('DOMContentLoaded', Base._checkFramework, false);
} else {
	document.attachEvent('readystatechange', Base._checkFramework);
};
