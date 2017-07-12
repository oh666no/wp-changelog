jQuery( document ).ready( function ($) {
	'use strict';
	//functions for search and sort features
	function tableSorting(){
		jQuery.extend({
			highlight: function (node, re, nodeName, className) {
				if (node.nodeType === 3) {
					var match = node.data.match(re);
					if (match) {
						var highlight = document.createElement(nodeName || 'span');
						highlight.className = className || 'highlight';
						var wordNode = node.splitText(match.index);
						wordNode.splitText(match[0].length);
						var wordClone = wordNode.cloneNode(true);
						highlight.appendChild(wordClone);
						wordNode.parentNode.replaceChild(highlight, wordNode);
						return 1; //skip added node in parent
					}
				} else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
						!/(script|style)/i.test(node.tagName) && // ignore script and style nodes
						!(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
					for (var i = 0; i < node.childNodes.length; i++) {
						i += jQuery.highlight(node.childNodes[i], re, nodeName, className);
					}
				}
				return 0;
			}
		});

		jQuery.fn.unhighlight = function (options) {
			var settings = { className: 'highlight', element: 'span' };
			jQuery.extend(settings, options);

			return this.find(settings.element + "." + settings.className).each(function () {
				var parent = this.parentNode;
				parent.replaceChild(this.firstChild, this);
				parent.normalize();
			}).end();
		};

		jQuery.fn.highlight = function (words, options) {
			var settings = { className: 'highlight', element: 'span', caseSensitive: false, wordsOnly: false };
			jQuery.extend(settings, options);
			
			if (words.constructor === String) {
				words = [words];
			}
			words = jQuery.grep(words, function(word, i){
			  return word != '';
			});
			words = jQuery.map(words, function(word, i) {
			  return word.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
			});
			if (words.length == 0) { return this; };

			var flag = settings.caseSensitive ? "" : "i";
			var pattern = "(" + words.join("|") + ")";
			if (settings.wordsOnly) {
				pattern = "\\b" + pattern + "\\b";
			}
			var re = new RegExp(pattern, flag);
			
			return this.each(function () {
				jQuery.highlight(this, re, settings.element, settings.className);
			});
		};
		
		var sortOptions = {
			valueNames: ['id', 'date', 'action', 'name', 'ver', 'type']
		};
		var sortedlist = new List('changelog-list', sortOptions);
		
		sortedlist.on("updated", function() {
		  
			$("td").unhighlight();
		  
			var search = $(".search").val();
			var words = search.split(" ");
			
			$('td').highlight(words);

		});
		
		$("#changelog-list input.search").on("change keyup paste",function(){
			if ($(this).val()){
				$(".wbl_reset").addClass("show");
			} else if (!$(this).val()){
				$(".wbl_reset").removeClass("show");
			}
		});
		
		//"reset filter" link controller
		$(".wbl_reset").click(function(){
			$("#changelog-list input.search").removeAttr('value');
			$(this).removeClass("show");
			$(".list-filter").val("all");
			reFilter("reset");
			sortedlist.search("");
		});
		
		$(".list-filter").each(function(){
			listFilteringInit($(this).data("target"));
		});
		
		//filtering selects builder
		function listFilteringInit(type){
			var i = 0;
			var typeList = {};
			var obj = [];
			$("td."+type).each(function(){
				obj.push($(this).html());
				obj = $.unique( obj ).sort();
				i++;
			});
			typeList[type] = obj;
			
			for ( i=0; i < typeList[type].length; ++i ){
				$(".list-filter[data-target="+type+"]").append("<option value="+typeList[type][i]+">"+typeList[type][i]+"</option>");
			}
		}
		
		//initial filtering state
		var filterValue = {type:"all",action:"all"}
		
		//filter input trigger
		$(".list-filter").on("change",function(){
			var type = $(this).data("target");
			filterValue[type] = $(this).val();
			
			//"reset filter" link controller
			if ( ( filterValue["type"] == "all" ) && ( filterValue["action"] == "all" ) && (!$("#changelog-list input.search").val()) ){
				$(".wbl_reset").removeClass("show");
			} else {
				$(".wbl_reset").addClass("show");				
			}
			reFilter();
		});
		
		//filtering option
		function reFilter(opt) {
			//use opt = reset to reset filter state
			if ( opt == "reset" ) {
				filterValue = {type:"all",action:"all"}
			}
			sortedlist.filter(function(item){
				//add a new checking for each new filter
				if ( ( (filterValue["type"] == item.values().type)||(filterValue["type"] == "all") ) && ( (filterValue["action"] == item.values().action)||(filterValue["action"] == "all") ) ){
					return true;
				} else {
					return false;
				}
			});
		}
	}
	tableSorting();
	
	//set switch value from hidden input
	$(".wbl_switch").each(function() {
		var $value = $(this).children(".switch_value").children("input").val();
		$("li",  $(this)).each(function(){
			if ((!$(this).hasClass("switch_value")) && ($(this).data("val") == $value)){
				$(this).addClass("active");
			}
		});
	});
	
	//active menu items control
	$(".wbl_menu li").click(function(){
		if (!$(this).hasClass("wbl_menu_section")){
			$(".wbl_menu li").removeClass("active");
			$(this).addClass("active");
			event.preventDefault();
		}
	});
	
	//tabs control
	$("a").click(function(){
		if ($(this).data("href")){
			var $tab = $(this).data("href");
			$(".wbl_tab").css("display","none");
			$("#"+$tab+".wbl_tab").css("display","block");
		}
	});
	
	//switch (radiobutton) control
	$(".wbl_switch li").click(function(){
		$(this).siblings().removeClass("active");
		$(this).addClass("active");
		$(this).siblings(".switch_value").children("input").attr("value",$(this).data("val"));
		event.preventDefault();
	});
	
	//message-boxes closing contol
	$(".wbl_success span,.wbl_error span,.wbl_warning span").click(function(){
		$(this).parent().fadeOut(300, function(){
			$(this).remove();
		});
	});
	
	//mobile menu generation
	function mobileMenu(){
		$(".wbl_menu li").each(function(){
			if (!$(this).hasClass("wbl_menu_section")){
				var $iconClass = $(this).children("a").attr("class");
				var $linkHref = $(this).children("a").attr("href");
				var $insertSpan = "<a href=\""+$linkHref+"\" class=\"wbl_mobile_link\"><span class=\"dashicons "+$iconClass+"\"></span></a>";
				$(this).children("a").before($insertSpan);
				$(this).children("a").not(".wbl_mobile_link").addClass("wbl_hide_mobile");
			} else {
				$(this).find("span").not(".wbl_counter").addClass("wbl_hide_mobile");
			}
		});
	}
	mobileMenu();
	
	function wblContentHeight(){
		var $height = $("#wbl_left_menu").height();

		if ( $height < $(".wbl_main").height() ){
			$height = $(".wbl_main").height();
		}
		if ( $height < $(".right_sidebar").height() ){
			$height = $(".right_sidebar").height();
		}
		$("#wbl_left_menu, .wbl_main, .right_sidebar").css("min-height", $height);
	}
		
});
