/**
 * Horizontal Stock Ticker for jQuery.
 * 
 * @package jStockTicker
 * @author Peter Halasz <skinner@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2009, Peter Halasz all rights reserved.
 */
(function(a){a.fn.jStockTicker=function(k){if(typeof(k)=="undefined"){k={}}var g=a.extend({},a.fn.jStockTicker.defaults,k);var h=a(this);g.tickerID=h[0].id;a.fn.jStockTicker.settings[g.tickerID]={};var l=null;if(h.parent().get(0).className!="wrap"){l=h.wrap("<div class='wrap'></div>")}var j=null;if(h.parent().parent().get(0).className!="container"){j=h.parent().wrap("<div class='container'></div>")}var f=h[0].firstChild;var i;while(f){i=f.nextSibling;if(f.NodeType==3){h[0].removeChild(f)}f=i}var c=h.children().first().outerWidth(true);a.fn.jStockTicker.settings[g.tickerID].shiftLeftAt=c;a.fn.jStockTicker.settings[g.tickerID].left=0;a.fn.jStockTicker.settings[g.tickerID].runid=null;h.width(2*screen.availWidth);function b(){e();var m=a.fn.jStockTicker.settings[g.tickerID];m.left-=g.speed;if(m.left<=m.shiftLeftAt*-1){m.left=0;h.append(h.children().get(0));m.shiftLeftAt=h.children().first().outerWidth(true)}h.css("left",m.left+"px");m.runId=setTimeout(arguments.callee,g.interval);a.fn.jStockTicker.settings[g.tickerID]=m}function e(){var m=a.fn.jStockTicker.settings[g.tickerID];if(m.runId){clearTimeout(m.runId)}m.runId=null;a.fn.jStockTicker.settings[g.tickerID]=m}function d(){e();b()}h.hover(e,b);b()};a.fn.jStockTicker.settings={};a.fn.jStockTicker.defaults={tickerID:null,url:null,speed:1,interval:20}})(jQuery);