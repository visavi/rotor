(function($) {
    "use strict";

    $.fn.markItUp = function(o, extraSettings) {
        let method, params, options, r, a, l;
        r = a = l = false; // ctrlKey, shiftKey, altKey

        if (typeof o === "string") {
            method = o;
            params = extraSettings;
        }

        options = {
            id: "",
            nameSpace: "",
            root: "",
            previewHandler: false,
            previewInWindow: "",
            previewInElement: "",
            previewAutoRefresh: true,
            previewPosition: "after",
            previewTemplatePath: "~/templates/preview.html",
            previewParser: false,
            previewParserPath: "",
            previewParserVar: "data",
            previewParserAjaxType: "POST",
            resizeHandle: true,
            beforeInsert: "",
            afterInsert: "",
            onEnter: {},
            onShiftEnter: {},
            onCtrlEnter: {},
            onTab: {},
            markupSet: [{}]
        };

        $.extend(options, o, extraSettings);

        if (!options.root) {
            $("script").each(function(e, t) {
                const r = $(t).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
                if (r !== null) {
                    options.root = r[1];
                }
            });
        }

        return this.each(function() {
            let $$, u, levels, scrollPosition, f, m, g, v, header, footer, previewWindow, template, iFrame, abort, I;
            $$ = $(this);
            u = this; // textarea
            levels = [];
            abort = false;
            scrollPosition = f = 0; // caretPosition
            m = -1; // caretOffset
            I = ""; // selection
            g = null; // clicked (button object)

            // Store plugin methods for access in call handlers
            const pluginMethods = {
                preview: preview,
                markup: markup,
                get: get,
                insert: insert,
                set: set
            };

            options.previewParserPath = localize(options.previewParserPath);
            options.previewTemplatePath = localize(options.previewTemplatePath);

            if (method) {
                switch (method) {
                    case "remove":
                        remove();
                        break;
                    case "insert":
                        markup(params);
                        break;
                    default:
                        $.error(`Method ${method} does not exist on jQuery.markItUp`);
                }
                return;
            } else {
                init();
            }

            function localize(e, t) {
                return t ? e.replace(/("|')~\//g, `$1${options.root}`) : e.replace(/^~\//, options.root);
            }

            function init() {
                let id = "", nameSpace = "";
                if (options.id) {
                    id = `id="${options.id}"`;
                } else if ($$.attr("id")) {
                    id = `id="markItUp${$$.attr("id").substr(0, 1).toUpperCase() + $$.attr("id").substr(1)}"`;
                }
                if (options.nameSpace) {
                    nameSpace = `class="${options.nameSpace}"`;
                }

                $$.wrap(`<div ${nameSpace}></div>`);
                $$.wrap(`<div ${id} class="markItUp"></div>`);
                $$.wrap('<div class="markItUpContainer"></div>');
                $$.addClass("markItUpEditor");

                // Store plugin methods in jQuery data
                $$.data('markItUp', pluginMethods);

                header = $('<div class="markItUpHeader"></div>').insertBefore($$);
                $(dropMenus(options.markupSet)).appendTo(header);

                footer = $('<div class="markItUpFooter"></div>').insertAfter($$);

                if (options.resizeHandle === true) {
                    const resizeHandle = $('<div class="markItUpResizeHandle"></div>').insertAfter($$).on("mousedown.markItUp", function(e) {
                        const i = $$.height(), n = e.clientY;
                        const t = function(e) {
                            $$.css("height", Math.max(20, e.clientY + i - n) + "px");
                            return false;
                        };
                        const r = function(e) {
                            $("html").off("mousemove.markItUp", t).off("mouseup.markItUp", r);
                            return false;
                        };
                        $("html").on("mousemove.markItUp", t).on("mouseup.markItUp", r);
                    });
                    footer.append(resizeHandle);
                }

                $$.on("keydown.markItUp", keyPressed).on("keyup", keyPressed);
                $$.on("insertion.markItUp", function(e, t) {
                    if (t.target !== false) {
                        get();
                    }
                    if (u === $.markItUp.focused) {
                        markup(t);
                    }
                });
                $$.on("focus.markItUp", function() {
                    $.markItUp.focused = this;
                });

                if (options.previewInElement) {
                    refreshPreview();
                }
            }

            function dropMenus(markupSet) {
                const ul = $("<ul></ul>");
                let i = 0;
                $("li:hover > ul", ul).css("display", "block");

                $.each(markupSet, function() {
                    const g = this; // button (minified as g)
                    let t = "", title, li, j;
                    title = g.title
                        ? g.key
                            ? (g.title || "") + ` [Ctrl+${g.key}]`
                            : (g.title || "")
                        : g.key
                            ? (g.name || "") + ` [Ctrl+${g.key}]`
                            : (g.name || "");
                    const key = g.key ? `accesskey="${g.key}"` : "";

                    if (g.separator) {
                        li = $(`<li class="markItUpSeparator">${g.separator || ""}</li>`).appendTo(ul);
                    } else {
                        i++;
                        for (j = levels.length - 1; j >= 0; j--) {
                            t += levels[j] + "-";
                        }
                        li = $(`<li class="markItUpButton markItUpButton${t + i} ${g.className || ""}"><a href="#" ${key} title="${title}">${g.name || ""}</a></li>`)
                            .on("contextmenu.markItUp", function() {
                                return false;
                            })
                            .on("click.markItUp", function(e) {
                                e.preventDefault();
                            })
                            .on("focusin.markItUp", function() {
                                $$.focus();
                            })
                            .on("mouseup", function(e) {
                                if (typeof g.call === "function") {
                                    g.call.call(this, e); // Ensure 'this' is the <a> element
                                }
                                setTimeout(() => markup(g), 1);
                                return false;
                            })
                            .on("mouseenter.markItUp", function() {
                                $("> ul", this).show();
                                $(document).one("click", () => $("ul ul", header).hide());
                            })
                            .on("mouseleave.markItUp", function() {
                                $("> ul", this).hide();
                            })
                            .appendTo(ul);

                        if (g.dropMenu) {
                            levels.push(i);
                            $(li).addClass("markItUpDropMenu").append(dropMenus(g.dropMenu));
                        }
                    }
                });
                levels.pop();
                return ul;
            }

            function magicMarkups(e) {
                if (!e) return "";
                e = e.toString();
                e = e.replace(/\(\!\(([\s\S]*?)\)\!\)/g, function(e, t) {
                    const r = t.split("|!|");
                    return l === true ? (r[1] !== undefined ? r[1] : r[0]) : (r[1] === undefined ? "" : r[0]);
                });
                e = e.replace(/\[\!\[([\s\S]*?)\]\!\]/g, function(e, t) {
                    const r = t.split(":!:");
                    if (abort === true) return false;
                    const value = prompt(r[0], r[1] ? r[1] : "");
                    if (value === null) {
                        abort = true;
                    }
                    return value;
                });
                return e;
            }

            function prepare(e) {
                if ($.isFunction(e)) {
                    e = e(v);
                }
                return magicMarkups(e);
            }

            function build(e) {
                const R = prepare(g.openWith);
                const x = prepare(g.placeHolder);
                const A = prepare(g.replaceWith);
                const F = prepare(g.closeWith);
                const L = prepare(g.openBlockWith);
                const N = prepare(g.closeBlockWith);
                const q = g.multiline;
                let block;

                if (A !== "") {
                    block = R + A + F;
                } else if (I === "" && x !== "") {
                    block = R + x + F;
                } else {
                    e = e || I;
                    let lines = [e], c = []; // Changed 'const l' to 'let lines'
                    if (q === true) {
                        lines = e.split(/\r?\n/);
                    }
                    for (let p = 0; p < lines.length; p++) {
                        let line = lines[p];
                        let u;
                        if ((u = line.match(/ *$/))) {
                            c.push(R + line.replace(/ *$/g, "") + F + u);
                        } else {
                            c.push(R + line + F);
                        }
                    }
                    block = c.join("\n");
                }
                block = L + block + N;
                return { block, openBlockWith: L, openWith: R, replaceWith: A, placeHolder: x, closeWith: F, closeBlockWith: N };
            }

            function markup(e) {
                let t, r, i, n, string, start;
                v = g = e;
                I = get();
                $.extend(v, {
                    line: "",
                    root: options.root,
                    textarea: u,
                    selection: I || "",
                    caretPosition: f,
                    ctrlKey: r,
                    shiftKey: a,
                    altKey: l
                });

                prepare(options.beforeInsert);
                prepare(g.beforeInsert);

                if ((r === true && a === true) || e.multiline === true) {
                    prepare(g.beforeMultiInsert);
                }

                $.extend(v, { line: 1 });

                if (r === true && a === true) {
                    const lines = I.split(/\r?\n/);
                    r = 0;
                    i = lines.length;
                    for (n = 0; n < i; n++) {
                        if ($.trim(lines[n]) !== "") {
                            $.extend(v, { line: ++r, selection: lines[n] });
                            lines[n] = build(lines[n]).block;
                        } else {
                            lines[n] = "";
                        }
                    }
                    string = { block: lines.join("\n") };
                    start = f;
                    t = string.block.length;
                } else if (r === true) {
                    string = build(I);
                    start = f + string.openWith.length;
                    t = string.block.length - string.openWith.length - string.closeWith.length;
                    t -= string.block.match(/ $/)? 1 : 0;
                } else if (a === true) {
                    string = build(I);
                    start = f;
                    t = string.block.length;
                } else {
                    string = build(I);
                    start = f + string.block.length;
                    t = 0;
                }

                if (I === "" && string.replaceWith === "") {
                    m += string.block.length;
                    start = f + string.openBlockWith.length + string.openWith.length;
                    t = string.block.length - string.openBlockWith.length - string.openWith.length - string.closeWith.length - string.closeBlockWith.length;
                    m = $$.val().substring(f, $$.val().length).length;
                }

                $.extend(v, { caretPosition: f, scrollPosition });

                if (string.block !== I && abort === false) {
                    insert(string.block);
                    set(start, t);
                } else {
                    m = -1;
                }

                get();
                $.extend(v, { line: "", selection: I });

                if ((r === true && a === true) || e.multiline === true) {
                    prepare(g.afterMultiInsert);
                }
                prepare(g.afterInsert);
                prepare(options.afterInsert);

                if (previewWindow && options.previewAutoRefresh) {
                    refreshPreview();
                }

                u.dispatchEvent(new Event("input"));
                a = l = r = abort = false;
            }

            function insert(e) {
                if (document.selection) {
                    document.selection.createRange().text = e;
                } else {
                    u.value = u.value.substring(0, f) + e + u.value.substring(f + I.length, u.value.length);
                }
            }

            function set(e, t) {
                if (u.createTextRange) {
                    const range = u.createTextRange();
                    range.collapse(true);
                    range.moveStart("character", e);
                    range.moveEnd("character", t);
                    range.select();
                } else if (u.setSelectionRange) {
                    u.setSelectionRange(e, e + t);
                }
                u.scrollTop = scrollPosition;
                u.focus();
            }

            function get() {
                "use strict";
                let T;
                u.focus();
                scrollPosition = u.scrollTop;
                if (document.selection) {
                    T = document.selection.createRange().text;
                    if ($.browser && $.browser.msie) {
                        const range = document.selection.createRange(), t = range.duplicate();
                        t.moveToElementText(u);
                        f = -1;
                        while (t.inRange(range)) {
                            t.moveStart("character");
                            f++;
                        }
                    } else {
                        f = u.selectionStart;
                    }
                } else {
                    f = u.selectionStart;
                    T = u.value.substring(f, u.selectionEnd);
                }
                I = T;
                return T;
            }

            function preview() {
                if (typeof options.previewHandler === "function") {
                    previewWindow = true;
                    options.previewHandler($$.val());
                } else if (options.previewInElement) {
                    previewWindow = $(options.previewInElement);
                    refreshPreview();
                } else if (!previewWindow || previewWindow.closed) {
                    if (options.previewInWindow) {
                        previewWindow = window.open("", "preview", options.previewInWindow);
                        $(window).on("unload", () => previewWindow.close());
                        refreshPreview();
                    } else {
                        iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
                        if (options.previewPosition === "after") {
                            iFrame.insertAfter(footer);
                        } else {
                            iFrame.insertBefore(header);
                        }
                        previewWindow = iFrame[iFrame.length - 1].contentWindow || iFrame[iFrame.length - 1];
                        refreshPreview();
                    }
                } else if (l === true) { // altKey to close preview
                    if (iFrame) {
                        iFrame.remove();
                    } else {
                        previewWindow.close();
                    }
                    previewWindow = iFrame = false;
                }

                if (!options.previewAutoRefresh) {
                    refreshPreview();
                }
                if (options.previewInWindow) {
                    previewWindow.focus();
                }
            }

            function refreshPreview() {
                renderPreview();
            }

            function renderPreview() {
                let t = $$.val();
                if (options.previewParser && typeof options.previewParser === "function") {
                    t = options.previewParser(t);
                }
                if (typeof options.previewHandler === "function") {
                    options.previewHandler(t);
                } else if (options.previewParserPath !== "") {
                    $.ajax({
                        type: options.previewParserAjaxType,
                        dataType: "text",
                        global: false,
                        url: options.previewParserPath,
                        data: `${options.previewParserVar}=${encodeURIComponent(t)}`,
                        success: function(e) {
                            writeInPreview(localize(e, 1));
                        },
                        error: function(err) {
                            console.error("Preview AJAX error:", err);
                        }
                    });
                } else if (!template) {
                    $.ajax({
                        url: options.previewTemplatePath,
                        dataType: "text",
                        global: false,
                        success: function(e) {
                            writeInPreview(localize(e, 1).replace(/<!-- content -->/g, t));
                        },
                        error: function(err) {
                            console.error("Template AJAX error:", err);
                        }
                    });
                }
            }

            function writeInPreview(e) {
                if (options.previewInElement) {
                    $(options.previewInElement).html(e);
                } else if (previewWindow && previewWindow.document) {
                    try {
                        const sp = previewWindow.document.documentElement.scrollTop;
                        previewWindow.document.open();
                        previewWindow.document.write(e);
                        previewWindow.document.close();
                        previewWindow.document.documentElement.scrollTop = sp;
                    } catch (err) {
                        console.error("Error writing to preview window:", err);
                    }
                }
            }

            function keyPressed(e) {
                a = e.shiftKey;
                l = e.altKey;
                r = (!e.altKey && !e.ctrlKey) && (e.ctrlKey || e.metaKey);

                if (e.type === "keydown") {
                    if (r === true) {
                        const li = $(`a[accesskey="${e.keyCode === 13 ? "\\n" : String.fromCharCode(e.keyCode)}"]`, header).parent("li");
                        if (li.length !== 0) {
                            r = false;
                            setTimeout(() => li.triggerHandler("mouseup"), 1);
                            return false;
                        }
                    }
                    if (e.keyCode === 13 || e.keyCode === 10) {
                        if (r === true) {
                            r = false;
                            markup(options.onCtrlEnter);
                            return options.onCtrlEnter.keepDefault;
                        }
                        if (a === true) {
                            a = false;
                            markup(options.onShiftEnter);
                            return options.onShiftEnter.keepDefault;
                        }
                        markup(options.onEnter);
                        return options.onEnter.keepDefault;
                    }
                    if (e.keyCode === 9) {
                        if (a !== true && r !== true && l !== true) {
                            if (m !== -1) {
                                get();
                                m = $$.val().length - m;
                                set(m, 0);
                                m = -1;
                                return false;
                            } else {
                                markup(options.onTab);
                                return options.onTab.keepDefault;
                            }
                        }
                    }
                }
            }

            function remove() {
                $$.off(".markItUp").removeClass("markItUpEditor");
                $$.parent("div").parent("div.markItUp").parent("div").replaceWith($$);
                const e = $$.parent("div").parent("div.markItUp").parent("div");
                if (e.length) {
                    e.replaceWith($$);
                }
                $$.data("markItUp", null);
            }
        });
    };

    $.fn.markItUpRemove = function() {
        return this.each(function() {
            $(this).markItUp("remove");
        });
    };

    $.markItUp = function(e) {
        const t = { target: false };
        $.extend(t, e);
        if (t.target) {
            return $(t.target).each(function() {
                $(this).focus();
                $(this).trigger("insertion", [t]);
            });
        } else {
            $("textarea").trigger("insertion", [t]);
        }
    };
})($);
