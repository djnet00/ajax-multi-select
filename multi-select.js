(function($) {
    'use strict';

    var methods = {

        init: function(options) {

            var settings = $.extend( {
                'maxSelect'     : 999,
                'url'			: 'http://localhost/',
                'autoOpen'		: true,
                'autoClear'		: true,
                'hideSelected'	: false,
                'minLength'		: 3,
                'requestType'   : 'GET',
                'classPrefix'   : '',
                'dataType'      : 'json',
                'requestData'   : {
                    action: 	'action'
                },
                'dataQuery'     : 'q',
                'respondVars'   : {
                    value     : 'value',
                    title     : 'title',
                    container : false
                }
            }, options);


            return this.each(attach);

            function attach() {

                var select 			= $(this);
                var wrapper			= $('<div>').addClass(settings.classPrefix + 'wrapper');
                var input 			= $('<input type="text">').addClass(settings.classPrefix + 'text-input');
                var list 			= $('<ul>').addClass(settings.classPrefix + 'menu-list').hide();
                var selectedList 	= $('<ul>').addClass(settings.classPrefix + 'selected-items');
                var load            = $('<li>').addClass(settings.classPrefix + 'loading');
                var openBtn            = $('<li>').addClass(settings.classPrefix + 'open').html('&#x25BC');

                var maxSelect 		= select.attr('data-max-select');
                maxSelect 			= (!maxSelect) ? settings.maxSelect : maxSelect;

                var currentSelected = -1;
                var selected 		= false;

                select.wrap(wrapper);

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

                selectedList.append(openBtn);
                selectedList.append(load);

                select.after(selectedList);
                selectedList.after(list);


                openBtn.click(function (e) {
                    e.stopPropagation();
                    list.is(":visible") ? hideList() : showList();
                });


                /**
                 * Add focus style to [selectedList] if input focus.
                 * Reset [currentSelected], [selected] and run load data from server
                 */
                input.focus(function(){
                    selectedList.addClass(settings.classPrefix + 'focused');
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
                    selectedList.removeClass(settings.classPrefix + 'focused');
                });

                /**
                 * Disable keyup action from arrow up, down and space.
                 * Start load data from server.
                 */
                input.keyup(function(e) {
                    switch(e.keyCode) {
                        case 38:
                        case 40:
                            showList();
                            return false;
                            break;
                        case 32:
                            return false;
                            break;

                        default:
                            loadData();
                            break;
                    }
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
                            if(selected === true) {
                                var input = list.find('li').eq(currentSelected)
                                    .find('input[type=checkbox]');
                                input.click();

                                return false;
                            }
                            break;
                    }
                });

                /**
                 * Hide menu and clear input value
                 *
                 */
                $(document).click(function() {
                    hideList();
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
                 * Remove selected item from selectedList
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
                    hideList();
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
                list.on('click', 'input', changeOption);
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
                    currentSelected++;
                    selectMove();
                }

                /**
                 * Set item selected style
                 */
                function selectMove() {
                    var lis = list.find('li');

                    currentSelected = (currentSelected < -1) ? -1 : currentSelected;

                    currentSelected = (currentSelected > (lis.length-1)) ? (lis.length-1) : currentSelected;

                    lis.removeClass(settings.classPrefix + 'selected');
                    selected = false;

                    if(currentSelected >= 0) {
                        lis.eq(currentSelected).addClass(settings.classPrefix + 'selected');
                        selected = true;
                    }
                }

                /**
                 * Add item to select list
                 */
                function addSelectedItem(value, title) {
                    var li = $('<li>').addClass(settings.classPrefix + 'item').attr('data-uid', value);
                    var a = $('<a>').attr('href', '#').html('&#x2715');
                    selectedList.prepend(li.text(title).append(a));
                    //selectedList.append(li.text(title).append(a));
                }

                /**
                 * Remove item from select list
                 */
                function removeSelectedItem(value) {
                    selectedList.find('li[data-uid="'+value+'"]').remove();
                }

                /**
                 * Disable all unselected items in menu, if selected more [maxSelect]
                 */
                function listDisable() {
                    if(select.children().length >= maxSelect) {
                        list.find('input').prop( "disabled", true ).parent().addClass(settings.classPrefix + 'disable');
                        $( "input:checked" ).prop( "disabled", false ).parent().removeClass(settings.classPrefix + 'disable');
                    }
                }

                /**
                 * Enable all items in menu
                 */
                function listEnable() {
                    list.find('input').prop( "disabled", false ).parent().removeClass(settings.classPrefix + 'disable');
                }

                /**
                 * Add option to select menu
                 */
                function addSelectOption(value, title) {
                    var item = select.find('option[value="'+value+'"]');
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
                    var item = select.find('option[value="'+value+'"]');
                    list.find('input[value="'+value+'"]').prop( "checked", false );
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
                        .val(value).addClass(settings.classPrefix + 'selectItem');
                    if(selected) {
                        input.prop('checked', true);
                    }
                    var span = $('<span>').text(title);

                    return li.append(label.append(input).append(span));
                }

                function propOption(value) {
                    return !select.find('option[value="'+value+'"]').val();
                }

                function showList() {
                    if(list.children().length === 0) {
                        list.append('<li>Not Found</li>');
                    }
                    list.css('top', selectedList.outerHeight());
                    list.css('width', selectedList.width());
                    list.show();
                    openBtn.html('&#x25B2');
                }

                function hideList() {
                    list.hide();
                    openBtn.html('&#x25BC');
                }

                function mergeOptions(obj1, obj2){
                    var obj3 = {};
                    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
                    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
                    return obj3;
                }
                /**
                 * Load data from server and create dropdown menu
                 */
                function loadData() {
                    if(input.val().length < settings.minLength) {
                        return false;
                    }
                    var requestData = {};
                    requestData[settings.dataQuery] = input.val();

                    load.show();

                    $.ajax({
                        type: 		settings.requestType,
                        dataType : 	settings.dataType,
                        url: 		settings.url,
                        data:       mergeOptions(
                                        requestData,
                                        settings.requestData
                                    ),
                        success: function(data) {
                            list.html('');
                            var container = (!settings.respondVars.container) ? data : data[settings.respondVars.container];
                            jQuery.each(container, function(key, val) {
                                if (propOption(val[settings.respondVars.value])) {
                                    list.append(createMenuItem(val[settings.respondVars.value],
                                        val[settings.respondVars.title]));
                                } else {
                                    if (!settings.hideSelected) {
                                        list.append(createMenuItem(val[settings.respondVars.value],
                                            val[settings.respondVars.title], true));
                                    }
                                }
                            });
                            listDisable();
                            load.hide();
                            if(list.children().length > 0) {
                                showList();
                            } else {
                                hideList();
                            }
                        },
                        error:  function(xhr, str) {

                        }
                    });
                }
            }
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
        }
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