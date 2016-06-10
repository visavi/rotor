// ----------------------------------------------------------------------------
// markItUp bb-code setting!
// ----------------------------------------------------------------------------
mySettings = {
	previewParserPath:	'', // path to your BBCode parser
	markupSet: [
		{title:'Жирный текст', name:'<i class="fa fa-bold"></i>', className:"bb-bold", key:'B', openWith:'[b]', closeWith:'[/b]'},
		{title:'Наклонный текст', name:'<i class="fa fa-italic"></i>', className:"bb-italic", key:'I', openWith:'[i]', closeWith:'[/i]'},
		{title:'Подчеркнутый текст', name:'<i class="fa fa-underline"></i>', className:"bb-underline", key:'U', openWith:'[u]', closeWith:'[/u]'},
		{title:'Зачеркнутый текст', name:'<i class="fa fa-strikethrough"></i>', className:"bb-strike", key:'S', openWith:'[s]', closeWith:'[/s]'},

		{separator:'---------------' },
		{title:'Ссылка', name:'<i class="fa fa-link"></i>', className:"bb-link", key:'L', openWith:'[url=[![Ссылка:!:http://]!]]', closeWith:'[/url]', placeHolder:'Текст ссылки...'},

		{title:'Изображение', name:'<i class="fa fa-image"></i>', className:"bb-image", openWith:'[img][![URL изображения:!:http://]!]', closeWith:'[/img]'},

		{title:'Видео', name:'<i class="fa fa-youtube-play"></i>', className:"bb-youtube", openWith:'[youtube][![Код видео с youtube]!]', closeWith:'[/youtube]'},
		{title:'Цвет', name:'<i class="fa fa-th"></i>', className:"bb-color", openWith:'[color=[![Код цвета]!]]', closeWith:'[/color]',
		dropMenu: [
			{name:'Yellow',	openWith:'[color=#ffd700]', closeWith:'[/color]', className:"col1-1" },
			{name:'Orange',	openWith:'[color=#ffa500]', closeWith:'[/color]', className:"col1-2" },
			{name:'Red', openWith:'[color=#ff0000]', closeWith:'[/color]', className:"col1-3" },

			{name:'Blue', openWith:'[color=#0000ff]', closeWith:'[/color]', className:"col2-1" },
			{name:'Purple', openWith:'[color=#800080]', closeWith:'[/color]', className:"col2-2" },
			{name:'Green', openWith:'[color=#00cc00]', closeWith:'[/color]', className:"col2-3" },

			{name:'Magenta', openWith:'[color=#ff00ff]', closeWith:'[/color]', className:"col3-1" },
			{name:'Gray', openWith:'[color=#808080]', closeWith:'[/color]', className:"col3-2" },
			{name:'Black', openWith:'[color=#00ffff]', closeWith:'[/color]', className:"col3-3" },
		]},

		{separator:'---------------' },
		{title:'Размер текста', name:'<i class="fa fa-font"></i>', className:"bb-size", openWith:'[size=[![Размер текста от 1 до 5]!]]', closeWith:'[/size]',
		dropMenu :[
			{name:'x-small', openWith:'[size=1]', closeWith:'[/size]' },
			{name:'small', openWith:'[size=2]', closeWith:'[/size]' },
			{name:'medium', openWith:'[size=3]', closeWith:'[/size]' },
			{name:'large', openWith:'[size=4]', closeWith:'[/size]' },
			{name:'x-large', openWith:'[size=5]', closeWith:'[/size]' },
		]},

		{title:'По центру', name:'<i class="fa fa-align-center"></i>', className:"bb-center", openWith:'[center]', closeWith:'[/center]'},
		{title:'Спойлер', name:'<i class="fa fa-text-height"></i>', className:"bb-spoiler", openWith:'[spoiler=[![Заголовок спойлера]!]]', closeWith:'[/spoiler]', placeHolder:'Текст спойлера...'},

		//{separator:'---------------' },
		//{name:'OrderedList', className:"bb-orderedlist", openWith:'[*]', multiline:true, openBlockWith:'[list=1]\n', closeBlockWith:'\n[/list]'},
		//{name:'UnorderedList', className:"bb-unorderedlist", openWith:'[*]', multiline:true, openBlockWith:'[list]\n', closeBlockWith:'\n[/list]'},
		//{name:'ListItem', className:"bb-listitem", openWith:'[*]'},

		{separator:'---------------' },
		{title:'Скрытый контент', name:'<i class="fa fa-eye-slash"></i>', className:"bb-hide", openWith:'[hide]', closeWith:'[/hide]'},
		{title:'Цитата', name:'<i class="fa fa-quote-right"></i>', className:"bb-quote", openWith:'[quote]', closeWith:'[/quote]'},
		{title:'Исходный код', name:'<i class="fa fa-code"></i>', className:"bb-code", openWith:'[code]', closeWith:'[/code]'},

		{separator:'---------------' },
		{title:'Очистка BB-кода', name:'<i class="fa fa-eraser"></i>', className:"bb-clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
		{title:'Смайл', name:'<i class="fa fa-smile-o"></i>', className:"bb-smile", openWith:' :) ',
		dropMenu: [
			{name:':)', openWith:' :) ', className:"col1-1" },
			{name:':(', openWith:' :( ', className:"col1-2" },
			{name:':E', openWith:' :E ', className:"col1-3" },
			{name:':hello', openWith:' :hello ', className:"col2-1" },
			{name:':cry', openWith:' :cry ', className:"col2-2" },
			{name:':obana', openWith:' :obana ', className:"col2-3" },
			{name:':infat', openWith:' :infat ', className:"col3-1" },
			{name:':klass', openWith:' :klass ', className:"col3-2" },
			{name:':krut', openWith:' :krut ', className:"col3-3" }
		]},
		//{title:'Просмотр', name:'<i class="fa fa-check-square-o"></i>', className:'bb-preview',  call:'preview'},
	]
}

// ----------------------------------------------------------------------------
// markItUp Html setting!
// ----------------------------------------------------------------------------
myHtmlSettings = {
	onShiftEnter:	{keepDefault:false, replaceWith:'<br />\r\n'},
	onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
	onTab:			{keepDefault:false, openWith:'	 '},
	markupSet: [
		{name:'Div', className:"div", openWith:'<div(!( class="[![Class]!]")!)>', closeWith:'</div>\n' },
		{name:'Span', className:"span", openWith:'<span(!( class="[![Class]!]")!)>', closeWith:'</span>\n' },
		{name:'Paragraph', className:"paragraph", openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>' },
		{separator:'---------------' },
		{name:'Heading 1', className:"heading1", key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'Введите название...' },
		{name:'Heading 2', className:"heading2", key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'Введите название...' },
		{name:'Heading 3', className:"heading3", key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'Введите название...' },
		{separator:'---------------' },
		{name:'Bold', className:"bold", key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
		{name:'Italic', className:"italic", key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)' },
		{name:'Stroke through', className:"strike", key:'S', openWith:'<del>', closeWith:'</del>' },
		{separator:'---------------' },
		{name:'Ul', className:"ul", openWith:'<ul>\n', closeWith:'</ul>\n' },
		{name:'Ol', className:"ol", openWith:'<ol>\n', closeWith:'</ol>\n' },
		{name:'Li', className:"li", openWith:'<li>', closeWith:'</li>' },
		{separator:'---------------' },
		{name:'Picture', className:"picture", key:'P', replaceWith:'<img src="[![Ссылка:!:http://]!]" alt="[![Альтернативный текст]!]" />' },
		{name:'Link', className:"link", key:'L', openWith:'<a href="[![Ссылка:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Текст ссылки...' },
		{separator:'---------------' },
		{name:'Clean', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } }
	]
}
