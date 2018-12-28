<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>test</title>
	<style type="text/css">
		.wrapper {
			position: relative;
		}

		.selected-items {
			list-style: none;
			width: 250px;
			padding: 3px;
			cursor: text;
			border-radius: 3px;
			border: solid 1px #ccc;
			background: #eeeeee; /* Old browsers */
			background: -moz-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* FF3.6-15 */
			background: -webkit-linear-gradient(top, #eeeeee 0%,#eeeeee 100%); /* Chrome10-25,Safari5.1-6 */
			background: linear-gradient(to bottom, #eeeeee 0%,#eeeeee 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eeeeee', endColorstr='#eeeeee',GradientType=0 ); /* IE6-9 */
		}

		.focused {
			box-shadow: inset 0 0 2px rgba(0,0,0,0.5);
		}

		.selected {
  			background-color: #ccc;
  			border-radius: 3px;
		}
		.selected-items>li {
			display: inline-block;
		}

		.selected-items>.item {
			font-size: 14px;
			padding: 3px 5px;
		}
		.selected-items>.item a {
			padding: 3px 5px;
			color: #000;
		}
		.selected-items>.item a:hover {
			font-weight: 900;
		}
		.selected-items>.item:hover {
			box-shadow: inset 0 0 2px rgba(0,0,0,0.5);
		}

		.selected-items a {
			text-decoration: none;
		}
		.menu-list {
			background-color: #fcfcfc;
			position: absolute;
			list-style: none;
			padding: 3px;
			border-radius: 3px;
			border: solid 1px #ccc;
			margin: 0;
			z-index: 999;
			box-shadow: 0 2px 4px rgba(0,0,0,0.5);
		}
		.menu-list>li {
			padding: 2px;
			margin: 3px;
		}
		.item {
			border-radius: 3px;
			border: solid 1px #ccc;
			padding: 2px;
			margin: 2px;
			background: #feffff; /* Old browsers */
			background: -moz-linear-gradient(top, #feffff 0%, #d2ebf9 100%); /* FF3.6-15 */
			background: -webkit-linear-gradient(top, #feffff 0%,#d2ebf9 100%); /* Chrome10-25,Safari5.1-6 */
			background: linear-gradient(to bottom, #feffff 0%,#d2ebf9 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#d2ebf9',GradientType=0 ); /* IE6-9 */
		}
		.text-input {
			border: none;
			background-color: transparent;
			width: 50px;
			font-size: 16px;
			padding: 3px;
			margin: 3px; 
		}
		.text-input:focus, .text-input:active {
			border: none;
			outline: none;
		}
		.disable {
			color: #ccc;
		}
	</style>
	<script src="jquery-3.2.1.js"></script>
	<script type="text/javascript" >
(function($) {
	'use strict'		

			
	var methods = {

		init: function(options) {

			var settings = $.extend( {
		      'maxSelect'       : 999,
		      'action'			: 'search_user',
		      'url'				: 'http://localhost/wp1/wp-admin/admin-ajax.php',
		      'autoOpen'		: true,
		      'autoClear'		: true,
		      'hideSelected'	: false,
		      'minLength'		: 0,
		    }, options);


			return this.each(attach);

			function attach() {

				var select 			= $(this);
			    var weapper			= $('<div>').addClass('wrapper');
			    var input 			= $('<input type="text">').addClass('text-input');
			    var list 			= $('<ul>').addClass('menu-list').hide();
			    var selectedList 	= $('<ul>').addClass('selected-items');

		    	var maxSelect 		= select.attr('data-max-select');
		    	maxSelect 			= (!maxSelect) ? settings.maxSelect : maxSelect;

		    	var currentSelected = -1;
		    	var selected 		= false;

		    	select.wrap(weapper);

		    	/**
		    	* Add default option to [selectedList]
		    	*/
		    	var defaultOption = select.children();
		    	if (defaultOption.length > 0) {
		    		defaultOption.each(function(index) {
		    			var option = $(this);
		    			option.attr('selected', 'true');
		    			addSelectedItem(option.val(), option.text());
		    		});
		    	}

		    	select.hide();
		    	
		    	selectedList.append($('<li>').append(input));	    	
		    	
		    	select.after(selectedList);
		    	selectedList.after(list);


		    	/**
		    	* Add focus style to [selectedList] if input focus.
		    	* Reswt [currentSelected], [selected] and run load data from server
		    	*/
		    	input.focus(function(){
		    		selectedList.addClass('focused');
		    		currentSelected = -1;
		    		selected = false;
		    		if(settings.autoOpen) {
		    			loadData();
		    		}
		    	});

		    	/**
		    	* Remove focus style from [selectedList] if input focusout
		    	*/
		    	input.focusout(function(){
		    		selectedList.removeClass('focused');
		    	});

		    	/**
		    	* Disable keyup action from arrow up, down and space.
		    	* Start load data srom server.
		    	*/
		    	input.keyup(function(e) {
		    		switch(e.keyCode) {
					  	case 38:								    
					    case 40:								    
					    case 32:
						  return false;
					    break;

					    default:
					    	loadData();
					    break;
					};
		    	});

		    	/**
		    	* Handling arrow and space keydown
		    	*/
		    	input.on('keydown', function(e){
					switch(e.keyCode) {
					  	case 38:
					  		selectDown();
						  	return false;
					    break;
								    
					    case 40:
					    	selectUp();
					    	return false;
					    break;
								    
					    case 32:
					    	if(selected == true) {
					    		var input = list.find('li').eq(currentSelected)
					    			.find('input[type=checkbox]');
					    		input.click();
					    		
					    		return false;
					    	}
					    break;
					};
				});

				/**
				* Hide menu and clear input value
				*
				*/
		    	$(document).click(function() {
		    		list.hide();
		    		if(settings.autoClear) {
		    			input.val('');
		    		}
		    	});

		    	list.click(function(event){
				    event.stopPropagation();
				});

				/**
				* Add selected item to selectedList
				*/
		    	select.on('DOMNodeInserted', function(e) {
				    var element = $(e.target);
				    addSelectedItem(element.val(), element.text());
				});

				/**
				* Remove seleted item from selectedList
				*/
				select.on('DOMNodeRemoved', function(e) {
				    var element = $(e.target);
				    removeSelectedItem(element.val());
				});

				/**
				* Handling click from remove link in selectedList
				*/
				selectedList.on('click', 'a', function() {
		    		var li = $(this).parent();
		    		removeSelectOption(li.attr('data-uid'));
		    		list.hide();
		    		return false;
		    	});

		    	/**
		    	* Handling click from selectedList
		    	* focus to input
		    	*/
		    	selectedList.click(function() {
		    		input.focus();
		    	});

		    	/**
		    	* Handling click from checkbox
		    	*/
		    	list.on('click', '.selectItem', changeOption);
		    	function changeOption() {
		    		var $this =  $(this);
		    		if($this.prop('checked')) {
		    			addSelectOption($this.val(), $this.next().text());
		    		} else {
		    			removeSelectOption($this.val());
		    		}
		    	}

		    	/**
		    	* Decrease position conter
		    	*/
		    	function selectDown() {
		    		currentSelected--;
		    		selectMove();
		    	}

		    	/**
		    	* Increase position conter
		    	*/
		    	function selectUp() {
		    		currentSelected++
		    		selectMove();
		    	}

		    	/**
		    	* Set item selected style
		    	*/
		    	function selectMove() {
		    		var lis = list.find('li');
		    		
		    		currentSelected = (currentSelected < -1) ? -1 : currentSelected;

  					currentSelected = (currentSelected > (lis.length-1)) ? (lis.length-1) : currentSelected;

  					lis.removeClass('selected');
  					selected = false;

  					if(currentSelected >= 0) {
  						lis.eq(currentSelected).addClass('selected');
  						selected = true;
  					}
		    	}

		    	/**
		    	* Add item to select list
		    	*/
		    	function addSelectedItem(value, title) {
		    		var li = $('<li>').addClass('item').attr('data-uid', value);
		    		var a = $('<a>').attr('href', '#').html('&#x2715');
		    		selectedList.prepend(li.text(title).append(a));
		    	}

		    	/**
		    	* Remove item from select list
		    	*/
		    	function removeSelectedItem(value) {
		    		selectedList.find('li[data-uid='+value+']').remove();
		    	}

		    	/**
		    	* Disable all unselected items in menu, if selected more [maxSelect]
		    	*/
		    	function listDisable() {
		    		if(select.children().length >= maxSelect) {
			    		list.find('input').prop( "disabled", true ).parent().addClass('disable');
			    		$( "input:checked" ).prop( "disabled", false ).parent().removeClass('disable');
		    		}
		    	}

		    	/**
		    	* Enable all items in menu
		    	*/
		    	function listEnable() {
		    		list.find('input').prop( "disabled", false ).parent().removeClass('disable');
		    	}

		    	/**
		    	* Add option to select menu
		    	*/
		    	function addSelectOption(value, title) {	    			
		    		var item = select.find('option[value='+value+']');
		    		if (!item.val()) {
		    			select.append($('<option>').val(value).text(title).attr('selected', 'true'));
		    			list.css('top', selectedList.outerHeight());
		    		}

		    		listDisable();
		    	}

		    	/**
		    	* Remove option from select menu
		    	*/
		    	function removeSelectOption(value) {
		    		var item = select.find('option[value='+value+']');
		    		item.remove();
		    		list.css('top', selectedList.outerHeight());
		    		listEnable();
		    	}

		    	/**
		    	* Create item for dropdown menu
		    	*/
		    	function createMenuItem(value, title, selected) {
		    		var li = $('<li>').attr('data-uid', value);
		    		var label = $('<label>');
		    		var input = $('<input>').attr('type', 'checkbox')
		    					.val(value).addClass('selectItem');
		    		if(selected) {
		    			input.prop('checked', true);
		    		}
		    		var span = $('<span>').text(title);

		    		return li.append(label.append(input).append(span));
		    	}

		    	function propOption(id) {
		    		return !select.find('option[value='+id+']').val();	
		    	}

		    	/**
		    	* Load data from server and create dropdown menu
		    	*/
		    	function loadData() {
		    		if(input.val().length < settings.minLength) {
		    			return false;
		    		}
		    		$.ajax({
		                type: 		"POST",
		                dataType : 	"json",
		                url: 		settings.url,
		                data: {
		                	action: 	settings.action,
		                	user_name: 	input.val()
		                }, 
		                success: function(data) {
		                	list.html('');
		                	jQuery.each(data, function(key, val) {
		                		
		                		var item = select.find('option[value='+val.ID+']');
		    					if (propOption(val.ID)) {
		    						list.append(createMenuItem(val.ID, val.title));
		                		} else {
		                			if (!settings.hideSelected) {
		                				list.append(createMenuItem(val.ID, val.title, true));
		                			}
		                		}
		                	});
		                	listDisable();
		                	if(list.children().length > 0) {
		                		list.css('top', selectedList.outerHeight());
		                		list.css('width', selectedList.width());
		                		list.show();
		                	} else {
		                		list.hide();
		                	}
		                },
		                error:  function(xhr, str) {

		                }
		            });
		    	};
	    	};
		},

		remove: function() {
			return this.each(function(){
				var select 			= $(this);
				var wrapper 		= select.parent();
				var selected 		= select.next();
				var menu 			= selected.next();

				select.unwrap();
				select.show();

				selected.remove();
				menu.remove();
				wrapper.remove();
			});
		},

		addSelected: function(data) {
			return this.each(function(){
				var select 			= $(this);

				select.append($('<option>').val(data.value)
					  .text(data.title).attr('selected', 'true'));
			});
		},
	};

	$.fn.selectWidget = function(method) {
		if ( methods[method] ) {
	      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
	    } else if ( typeof method === 'object' || ! method ) {
	      return methods.init.apply( this, arguments );
	    } else {
	      $.error( 'Unknown method: ' +  method );
	    } 
	};

})(jQuery);
	</script>
</head>
<body>

<div>
	<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
	tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
	quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
	consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
	cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
	proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</div>

		<select multiple="" class="users" id="select-1"></select>
	
	
	<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
	tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
	quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
	consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
	cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
	proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</div>
	
	<div>
		<select multiple="" class="users" id="select-2" data-max-select="2">
			<option value="1" selected="selected">admin</option>
		</select>
	</div>
</div>

<script>
$('.users').selectWidget({
	'maxSelect' 	: 2,
	'autoOpen'  	: true,
	'autoClear' 	: false,
	'hideSelected' 	: false,
	'minLength' 	: 0,
});

$('#select-1').selectWidget('addSelected', {value:1, title:'admin'});
</script>
</body>
</html>