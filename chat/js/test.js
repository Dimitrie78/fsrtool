jQuery.noConflict(); 
jQuery.noConflict(); 
jQuery(document).ready(function($){
		//button hover
		$('#start_chat').hover(
			function(){
				$('img',this).attr('src','/js/jquerychat/btn-try-b.png');
			},
			function(){
				$('img',this).attr('src','/js/jquerychat/btn-try-a.png');
			}	
		);
		
		$('#submit_btn').hover(
			function(){
				$(this).attr('src','/js/jquerychat/btn-buy-b.png');
			},
			function(){
				$(this).attr('src','/js/jquerychat/btn-buy-a.png');
			}	
		);
		
		
		//DEMO CHAT START
		$('#start_chat').click(function(){
			//check if chat's not already popped up
			if($('.chatbox').length == 0){
			
				//open chat window
				$('body').append('<div class="chatbox" id="chat_window" title="Demo Bot">'+
					'<div class="header" title="Demo Bot">'+
						'<p>Demo Bot</p>'+
						'<a href="#" class="close_chatbox" title="close chat window">X</a>'+
						'<a href="#" class="minimize_chatbox" title="minimize chat window">_</a>'+
						'<a href="#" class="maximize_chatbox" title="maximize chat window">&#8254;</a>'+
					'</div>'+
					'<div class="chat_area" title="Demo Bot">'+
					'</div>'+
					'<div class="chat_info"><p></p></div>'+					
					'<div class="chat_message" title="Type your message here">'+
						'<textarea></textarea>'+
					'</div>'+
				'</div>');
			
				//display welcome messages
				setTimeout(function(){
					printChat('<p><b>Demo Bot: </b>Hello!</p>');		
					
					botMsg('I\'m a demo bot and I\'ll repeat what you write! :)');
				}, 600);

				
			
			}	
			
			return false;
		})
		
		
		//ADD TO CHAT AREA
		function printChat(text){
			//replace smileys
			text = text.replace(':)','<img src="/js/jquerychat/smileys-smile.png" />');
			text = text.replace(':(','<img src="/js/jquerychat/smileys-sad.png" />');
			text = text.replace(':D','<img src="/js/jquerychat/smileys-d.png" />');
			text = text.replace(':d','<img src="/js/jquerychat/smileys-d.png" />');
			text = text.replace(':love','<img src="/js/jquerychat/smileys-love.png" />');
			text = text.replace(':p','<img src="/js/jquerychat/smileys-p.png" />');
			text = text.replace(':P','<img src="/js/jquerychat/smileys-p.png" />');
			
			//replace links
			var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
			text = text.replace(exp,"<a href='$1' target=\"_blank\">$1</a>"); 
						
			
			$('#chat_window .chat_area').append(text);				

			//if nu message, scroll to bottom
			$('#chat_window .chat_area').animate({scrollTop: 9999999},200);
		}
		
		//BOT ANSWER
		function botMsg(text){
			//is typing for 2 sec but wait a sec to react
			
			setTimeout(function(){			
				$('#chat_window .chat_info p').text('Demo Bot is typing...');
				
				//print message
				setTimeout(function(){				
					//remove is typing also
					$('#chat_window .chat_info p').text('');
						
					//print msg
					printChat('<p><b>Demo Bot: </b>'+text+'</p>');				
				}, 2000);
				
			}, 700);			
		}
		
		
		
		//SEND USER MESSAGE
		$('.chat_message textarea').live('keypress', function (e) {
				if (e.keyCode == 13 && !e.shiftKey) {
					e.preventDefault();				

					var msg = $(this).val();
					//remove HTML
					msg = msg.replace(/<(?:.|\n)*?>/gm, '');
					
					
					//remove text from textarea
					$(this).val('');
					
					//add to chat area
					printChat('<p class="me"><b>Me: </b>'+msg+'</p>');
					
					//bot answers
					botMsg(msg);
				}
		});
		
		
		//MINIMIZE WINDOW
		$('.minimize_chatbox').live('click',function(){
			//remove chat,message area			
			$(this).closest('.chatbox').find('.chat_area,.chat_message,.chat_info').css('height','0px');		
			$(this).closest('.chatbox').css('height','25px');
				
			//replace minimize icon
			$(this).css('display','none');
			$(this).closest('.chatbox').find('.maximize_chatbox').css('display','inline');
			
			return false;
		});
		
		
		//MAXIMIZE WINDOW
		$('.maximize_chatbox').live('click',function(){
			//remove chat,message area			
			$(this).closest('.chatbox').find('.chat_area').css('height','180px');		
			$(this).closest('.chatbox').find('.chat_message').css('height','55px');		
			$(this).closest('.chatbox').find('.chat_info').css('height','20px');		
			$(this).closest('.chatbox').css('height','300px');
				
			//replace minimize icon
			$(this).css('display','none');
			$(this).closest('.chatbox').find('.minimize_chatbox').css('display','inline');
			$(this).closest('.chatbox').find('.header .new_message').remove();
			
			return false;
		});
		
		
		//CLOSE WINDOW
		$('.close_chatbox').live('click',function(){
			$(this).closest('.chatbox').remove();						
				
			return false;
		});		
		
		
		
});
