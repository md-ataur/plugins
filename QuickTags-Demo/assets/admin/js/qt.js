;(function($){
	$(document).ready(function(){
		QTags.addButton('qt-button-one','U','<ul>','</ul>');
		QTags.addButton('qt-button-two','JS',qt_button_two);

		function qt_button_two(){
			var name = prompt('What is your name?');
			var text = "Hello "+name;
			QTags.insertContent(text);
		}
	});
})(jQuery);