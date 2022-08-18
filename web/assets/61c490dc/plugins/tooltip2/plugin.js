/**
 * Created by na on 22.05.2017.
 */
/**
 * 90% этого плагина взято из плагина ссылок.
 * Тут много неиспользуемого кода, который надо перебратьи поудалять.
 */
CKEDITOR.plugins.add("tooltip2", {
    requires: "dialog,fakeobjects",
    icons: 'tooltip2',
    lang: ['ru','en'],
    onLoad: function() {
        function b(b) {
            return d.replace(/%1/g, "rtl" == b ? "right" : "left").replace(/%2/g, "cke_contents_" + b)
        }
        var a = "background:url(" + CKEDITOR.getUrl(this.path + "images" + (CKEDITOR.env.hidpi ? "/hidpi" : "") + "/anchor.png") + ") no-repeat %1 center;border:1px dotted #00f;background-size:16px;",
            d = ".%2 a.cke_anchor,.%2 a.cke_anchor_empty,.cke_editable.%2 a[name],.cke_editable.%2 a[data-cke-saved-name]{" + a + "padding-%1:18px;cursor:auto;}" + (CKEDITOR.plugins.link.synAnchorSelector ?
                    "span.cke_anchor_empty{display:inline-block;}" : "") + ".%2 img.cke_anchor{" + a + "width:16px;min-height:15px;height:1.15em;vertical-align:" + (CKEDITOR.env.opera ? "middle" : "text-bottom") + ";}";
        CKEDITOR.addCss(b("ltr") + b("rtl"))
    },
    init: function(b) {
        var a = "span(!b-tooltip)[!tooltip_id]";
        b.addCommand("tooltip2", new CKEDITOR.dialogCommand("tooltip2", {
            allowedContent: a,
            requiredContent: "span(!b-tooltip)[tooltip_id]"
        }));
        b.setKeystroke(CKEDITOR.CTRL + 76, "tooltip2");
        b.ui.addButton && (b.ui.addButton("tooltip2", {
            label: b.lang.tooltip2.popup_text,
            command: "tooltip2",
            toolbar: "tooltip2s,10"
        }));
        CKEDITOR.dialog.add("tooltip2", this.path + "dialogs/tooltip2.js");

        b.addMenuItems && b.addMenuItems({
            link: {
                label: b.lang.link.menu,
                command: "link",
                group: "link",
                order: 1
            }
        });
        b.contextMenu && b.contextMenu.addListener(function(a) {
            if (!a || a.isReadOnly()) return null;
            a = CKEDITOR.plugins.link.tryRestoreFakeAnchor(b,
                a);
            if (!a && !(a = CKEDITOR.plugins.link.getSelectedLink(b))) return null;
            var c = {};
            a.getAttribute("tooltip_id") && a.getChildCount() && (c = {
                link: CKEDITOR.TRISTATE_OFF,
                unlink: CKEDITOR.TRISTATE_OFF
            });
            if (a && a.hasAttribute("name")) c.anchor = c.removeAnchor = CKEDITOR.TRISTATE_OFF;
            return c
        })
    },
    afterInit: function(b) {
        var a = b.dataProcessor,
            d = a && a.dataFilter,
            a = a && a.htmlFilter,
            c = b._.elementsPath && b._.elementsPath.filters;
        d && d.addRules({
            elements: {
                a: function(a) {
                    var c = a.attributes;
                    if (!c.name) return null;
                    var d = !a.children.length;
                    if (CKEDITOR.plugins.link.synAnchorSelector) {
                        var a =
                                d ? "cke_anchor_empty" : "cke_anchor",
                            e = c["class"];
                        if (c.name && (!e || 0 > e.indexOf(a))) c["class"] = (e || "") + " " + a;
                        d && CKEDITOR.plugins.link.emptyAnchorFix && (c.contenteditable = "false", c["data-cke-editable"] = 1)
                    } else if (CKEDITOR.plugins.link.fakeAnchor && d) return b.createFakeParserElement(a, "cke_anchor", "anchor");
                    return null
                }
            }
        });
        CKEDITOR.plugins.link.emptyAnchorFix && a && a.addRules({
            elements: {
                a: function(a) {
                    delete a.attributes.contenteditable
                }
            }
        });
        c && c.push(function(a, c) {
            if ("span" == c && (CKEDITOR.plugins.link.tryRestoreFakeAnchor(b,
                    a) || a.getAttribute("name") && (!a.getAttribute("tooltip_id") || !a.getChildCount()))) return "anchor"
        })
    }
});

CKEDITOR.plugins.tooltip2 = {
    getSelectedLink: function(b) {
        var a = b.getSelection(),
            d = a.getSelectedElement();
        return d && d.is("span") ? d : (a = a.getRanges()[0]) ? (a.shrink(CKEDITOR.SHRINK_TEXT), b.elementPath(a.getCommonAncestor()).contains("span", 1)) : null
    }
};