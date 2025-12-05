// ----------------------------------------------------------------------------
// markItUp bb-code setting!
// ----------------------------------------------------------------------------
window.mySettings = {
    previewParserPath: '/ajax/bbcode', // path to your BBCode parser
    previewAutoRefresh: false,
    onTab: {keepDefault: false, openWith: '    '},
    markupSet: [
        {title: __('editor.bold'), name: '<i class="fa fa-bold"></i>', className: 'bb-bold', key: 'B', openWith: '[b]', closeWith: '[/b]'},
        {title: __('editor.italic'), name: '<i class="fa fa-italic"></i>', className: 'bb-italic', key: 'I', openWith: '[i]', closeWith: '[/i]'},
        {title: __('editor.underline'), name: '<i class="fa fa-underline"></i>', className: 'bb-underline', key: 'U', openWith: '[u]', closeWith: '[/u]'},
        {title: __('editor.strike'), name: '<i class="fa fa-strikethrough"></i>', className: 'bb-strike', key: 'S', openWith: '[s]', closeWith: '[/s]'},

        {separator: '---------------'},
        {title: __('editor.link'), name: '<i class="fa fa-link"></i>', className: 'bb-link', key: 'L', openWith: '[url=[![' + __('editor.link') + ':!:https://]!]]', closeWith: '[/url]', placeHolder: __('editor.link_text')},

        {title: __('editor.image'), name: '<i class="fa fa-image"></i>', className: 'bb-image', openWith: '[img][![' + __('editor.image_text') + ':!:https://]!]', closeWith: '[/img]'},

        {title: __('editor.video'), name: '<i class="fab fa-youtube"></i>', className: 'bb-video', openWith: '[video][![' + __('editor.video_link') + ':!:https://]!]', closeWith: '[/video]'},

        {title: __('editor.audio'), name: '<i class="fa-solid fa-music"></i>', className: 'bb-audio', openWith: '[audio][![' + __('editor.audio_link') + ':!:https://]!]', closeWith: '[/audio]'},

        {separator: '---------------'},
        {title: __('editor.color'), name: '<i class="fa fa-th"></i>', className: 'bb-color', openWith: '[color=[![' + __('editor.color_code') + ']!]]', closeWith: '[/color]',
        dropMenu: [
            {name: 'Yellow', openWith: '[color=#ffd700]', closeWith: '[/color]', className: 'col1-1'},
            {name: 'Orange', openWith: '[color=#ffa500]', closeWith: '[/color]', className: 'col1-2'},
            {name: 'Red', openWith: '[color=#ff0000]', closeWith: '[/color]', className: 'col1-3'},

            {name: 'Blue', openWith: '[color=#0000ff]', closeWith: '[/color]', className: 'col2-1'},
            {name: 'Purple', openWith: '[color=#800080]', closeWith: '[/color]', className: 'col2-2'},
            {name: 'Green', openWith: '[color=#00cc00]', closeWith: '[/color]', className: 'col2-3'},

            {name: 'Magenta', openWith: '[color=#ff00ff]', closeWith: '[/color]', className: 'col3-1'},
            {name: 'Gray', openWith: '[color=#808080]', closeWith: '[/color]', className: 'col3-2'},
            {name: 'Cyan', openWith: '[color=#00ffff]', closeWith: '[/color]', className: 'col3-3'}
        ]},

        {title: __('editor.font_size'), name: '<i class="fa fa-font"></i>', className: 'bb-size', openWith: '[size=[![' + __('editor.font_text') + ']!]]', closeWith: '[/size]',
        dropMenu :[
            {name: 'x-small', openWith: '[size=1]', closeWith: '[/size]'},
            {name: 'small', openWith: '[size=2]', closeWith: '[/size]'},
            {name: 'medium', openWith: '[size=3]', closeWith: '[/size]'},
            {name: 'large', openWith: '[size=4]', closeWith: '[/size]'},
            {name: 'x-large', openWith: '[size=5]', closeWith: '[/size]'}
        ]},

        {
            title: __('editor.sticker'),
            name: '<i class="fa fa-smile"></i>',
            className: 'bb-sticker',
            beforeInsert: function () {
                const stickerModal = $('#stickersModal');
                //const stikerModal = document.getElementById('stickersModal');
                if (stickerModal.length) {
                    /*const modal = new bootstrap.Modal(stikerModal);
                    modal.show();*/
                    $('#stickersModal').modal('show')
                    return false;
                }

                $.ajax({
                    dataType: 'json',
                    type: 'get',
                    url: '/ajax/getstickers',
                    success: function(data) {
                        if (data.success && data.stickers) {
                            $('body').append(data.stickers);
                            const newModal = $('#stickersModal');
                            //const newModal = document.getElementById('stickersModal');
                            if (newModal.length) {
                                /*const modal = new bootstrap.Modal(newModal);
                                modal.show();*/
                                $('#stickersModal').modal('show')
                            }
                        }
                    },
                });

                return false;
            }
        },

        {separator: '---------------'},
        {title: __('editor.spoiler'), name: '<i class="fa fa-plus-square"></i>', className: 'bb-spoiler', openWith: '[spoiler=[![' + __('editor.spoiler_title') + ']!]]', closeWith: '[/spoiler]', placeHolder: __('editor.spoiler_text')},
        {title: __('editor.hide'), name: '<i class="fa fa-eye-slash"></i>', className: 'bb-hide', openWith: '[hide]', closeWith: '[/hide]'},
        {title: __('editor.quote'), name: '<i class="fa fa-quote-right"></i>', className: 'bb-quote', openWith: '[quote]', closeWith: '[/quote]'},
        {title: __('editor.code'), name: '<i class="fa fa-code"></i>', className: 'bb-code', openWith: '[code]', closeWith: '[/code]'},

        {separator: '---------------'},
        {title: __('editor.center'), name: '<i class="fa fa-align-center"></i>', className: 'bb-center', openWith: '[center]', closeWith: '[/center]'},
        {title: __('editor.unorderedlist'), name: '<i class="fa fa-list-ul"></i>', className: 'bb-unorderedlist', multiline:true, openBlockWith: '[list]\n', closeBlockWith: '\n[/list]', placeHolder: __('editor.list_text')},
        {title: __('editor.orderedlist'), name: '<i class="fa fa-list-ol"></i>', className: 'bb-orderedlist', multiline:true, openBlockWith: '[list=1]\n', closeBlockWith: '\n[/list]', placeHolder: __('editor.list_text')},

        {separator: '---------------'},
        {title: __('editor.clean'), name: '<i class="fa fa-eraser"></i>', className: 'bb-clean', replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
        /*{title: __('editor.preview, name: '<i class="fa fa-check-square"></i>', classname: 'bb-preview',  call: 'preview'},*/
        {
            title: __('editor.preview'),
            name: '<i class="fa fa-check-square"></i>',
            className: 'bb-preview',
            call: function(e) {
                const markItUp = $(this).closest('.markItUp').find('textarea').data('markItUp');
                if (markItUp && markItUp.preview) {
                    markItUp.preview();
                }
            }
        }
    ]
};

// ----------------------------------------------------------------------------
// markItUp Html setting!
// ----------------------------------------------------------------------------
window.myHtmlSettings = {
    onCtrlEnter: {keepDefault: false, replaceWith: '<br />\r'},
    onTab: {keepDefault: false, openWith: '    '},
    markupSet: [
        {title: 'Div', name: '<i class="fa fa-list-alt"></i>', className: 'bb-div', openWith: '<div(!( class="[![Class]!]")!)>', closeWith: '</div>\n'},
        {title: 'Span', name: '<i class="fa fa-columns"></i>', className: 'bb-span', openWith: '<span(!( class="[![Class]!]")!)>', closeWith: '</span>\n'},
        {title: 'Paragraph', name: '<i class="fa fa-paragraph"></i>', className: 'bb-paragraph', openWith: '<p(!( class="[![Class]!]")!)>', closeWith: '</p>\n'},
        {title: 'Table', name: '<i class="fa fa-table"></i>', className: 'bb-table', openWith: '<table(!( class="[![Class]!]")!)>\n	<tr>\n		<td>', closeWith: '</td>\n	</tr>\n</table>'},
        {separator: '---------------'},
        {title: 'Heading 1', name: '<i class="fa fa-heading">1</i>', className: 'bb-heading1', key: '1', openWith: '<h1(!( class="[![Class]!]")!)>', closeWith: '</h1>', placeHolder: __('editor.enter_title')},
        {title: 'Heading 2', name: '<i class="fa fa-heading">2</i>', className: 'bb-heading2', key: '2', openWith: '<h2(!( class="[![Class]!]")!)>', closeWith: '</h2>', placeHolder: __('editor.enter_title')},
        {title: 'Heading 3', name: '<i class="fa fa-heading">3</i>', className: 'bb-heading3', key: '3', openWith: '<h3(!( class="[![Class]!]")!)>', closeWith: '</h3>', placeHolder: __('editor.enter_title')},
        {separator: '---------------'},
        {title: __('editor.bold'), name: '<i class="fa fa-bold"></i>', className: 'bb-bold', key: 'B', openWith: '(!(<strong>|!|<b>)!)', closeWith: '(!(</strong>|!|</b>)!)'},
        {title: __('editor.italic'), name: '<i class="fa fa-italic"></i>', className: 'bb-italic', key: 'I', openWith: '(!(<em>|!|<i>)!)', closeWith: '(!(</em>|!|</i>)!)'},
        {title: __('editor.underline'), name: '<i class="fa fa-underline"></i>', className: 'bb-underline', key: 'U', openWith: '<u>', closeWith: '</u>'},
        {title: __('editor.strike'), name: '<i class="fa fa-strikethrough"></i>', className: 'bb-strike', key: 'S', openWith: '<del>', closeWith: '</del>'},
        {separator: '---------------'},
        {title: 'Ul', name: '<i class="fa fa-list-ul"></i>', className: 'bb-ul', openWith: '<ul>\n', closeWith: '</ul>\n'},
        {title: 'Ol', name: '<i class="fa fa-list-ol"></i>', className: 'bb-ol', openWith: '<ol>\n', closeWith: '</ol>\n'},
        {title: 'Li', name: '<i class="fa fa-minus"></i>', className: 'bb-li', openWith: '<li>', closeWith: '</li>'},
        {separator: '---------------'},
        {title: __('editor.image'), name: '<i class="fa fa-image"></i>', className: 'bb-picture', key: 'P', replaceWith: '<img src="[![' + __('editor.image_text') + ':!:https://]!]" alt="[![' + __('editor.alt') + ']!]" />'},
        {title: __('editor.link'), name: '<i class="fa fa-link"></i>', className: 'bb-link', key: 'L', openWith: '<a href="[![' + __('editor.link') + ':!:https://]!]"(!( title="[![Title]!]")!)>', closeWith: '</a>', placeHolder: __('editor.link_text')},
        {separator: '---------------'},
        {title: __('editor.clean'), name: '<i class="fa fa-eraser"></i>', classname: 'bb-clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } }
    ]
};
