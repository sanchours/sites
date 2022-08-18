CKEDITOR.dialog.add("tooltip2", function(n) {
	var p, q;
	var html_block = '<div id="tooltip_iframe_div_'+n.id+'" style="width:100%;height:600px"><iframe id="tooltip_iframe_'+n.id+'" style="width:100%;min-width:750px;height:600px" width="750" height="600" src=""></iframe></div>';

	function r(a) {
		return a.replace(/'/g, "\\$&")
	}

	function t(a) {
		var g, c = p,
			d, e;
		g = [q, "("];
		for (var b = 0; b < c.length; b++) d = c[b].toLowerCase(), e = a[d], 0 < b && g.push(","), g.push("'", e ? r(encodeURIComponent(a[d])) : "", "'");
		g.push(")");
		return g.join("")
	}

	function u(a) {
		for (var g, c = a.length, d = [], e = 0; e < c; e++) g = a.charCodeAt(e), d.push(g);
		return "String.fromCharCode(" + d.join(",") + ")"
	}

	function v(a) {
		return (a = a.getAttribute("class")) ? a.replace(/\s*(?:cke_anchor_empty|cke_anchor)(?:\s*$)?/g,
			"") : ""
	}
	var w = CKEDITOR.plugins.tooltip2,
		s = function() {
			var a = this.getDialog(),
				g = a.getContentElement("target", "popupFeatures"),
				a = a.getContentElement("target", "linkTargetName"),
				c = this.getValue();
			if (g && a) switch (g = g.getElement(), g.hide(), a.setValue(""), c) {
				case "frame":
					a.setLabel(n.lang.link.targetFrameName);
					a.getElement().show();
					break;
				case "popup":
					g.show();
					a.setLabel(n.lang.link.targetPopupName);
					a.getElement().show();
					break;
				default:
					a.setValue(c), a.getElement().hide()
			}
		},
		x = /^javascript:/,
		y = /^mailto:([^?]+)(?:\?(.+))?$/,
		z = /subject=([^;?:@&=$,\/]*)/,
		A = /body=([^;?:@&=$,\/]*)/,
		B = /^#(.*)$/,
		C = /^((?:http|https|ftp|news):\/\/)?(.*)$/,
		D = /^(_(?:self|top|parent|blank))$/,
		E = /^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,
		F = /^javascript:([^(]+)\(([^)]+)\)$/,
		G = /\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/,
		H = /(?:^|,)([^=]+)=(\d+|yes|no)/gi,
		j = function(a) {
			a.target && this.setValue(a.target[this.id] || "")
		},
		k = function(a) {
			a.adv && this.setValue(a.adv[this.id] || "")
		},
		l = function(a) {
			a.target || (a.target = {});
			a.target[this.id] = this.getValue() ||
				""
		},
		I = function(a, g) {
			var c = g && (g.data("cke-saved-href") || g.getAttribute("tooltip_id")) || "",
				d, e, b = {};
			c.match(x) && ("encode" == o ? c = c.replace(E, function(a, c, b) {
				return "mailto:" + String.fromCharCode.apply(String, c.split(",")) + (b && b.replace(/\\'/g, "'"))
			}) : o && c.replace(F, function(a, c, d) {
				if (c == q) {
					b.type = "email";
					for (var a = b.email = {}, c = /(^')|('$)/g, d = d.match(/[^,\s]+/g), e = d.length, g, f, h = 0; h < e; h++) g = decodeURIComponent, f = d[h].replace(c, "").replace(/\\'/g, "'"), f = g(f), g = p[h].toLowerCase(), a[g] = f;
					a.address = [a.name, a.domain].join("@")
				}
			}));
			if (!b.type)
				if (d = c.match(B)) b.type = "anchor", b.anchor = {}, b.anchor.name = b.anchor.id =
					d[1];
				else if (d = c.match(y)) {
					e = c.match(z);
					c = c.match(A);
					b.type = "email";
					var f = b.email = {};
					f.address = d[1];
					e && (f.subject = decodeURIComponent(e[1]));
					c && (f.body = decodeURIComponent(c[1]))
				} else c && (e = c.match(C)) ? (b.type = "url", b.url = {}, b.url.protocol = e[1], b.url.url = e[2]) : b.type = "url";
			if (g) {
				d = g.getAttribute("target");
				b.target = {};
				b.adv = {};
				if (d) d.match(D) ? b.target.type = b.target.name = d : (b.target.type = "frame", b.target.name = d);
				else if (d = (d = g.data("cke-pa-onclick") || g.getAttribute("onclick")) && d.match(G)) {
					b.target.type =
						"popup";
					for (b.target.name = d[1]; c = H.exec(d[2]);)("yes" == c[2] || "1" == c[2]) && !(c[1] in {
						height: 1,
						width: 1,
						top: 1,
						left: 1
					}) ? b.target[c[1]] = !0 : isFinite(c[2]) && (b.target[c[1]] = c[2])
				}
				d = function(a, c) {
					var d = g.getAttribute(c);
					null !== d && (b.adv[a] = d || "")
				};
				d("advId", "id");
				d("advLangDir", "dir");
				d("advAccessKey", "accessKey");
				b.adv.advName = g.data("cke-saved-name") || g.getAttribute("name") || "";
				d("advLangCode", "lang");
				d("advTabIndex", "tabindex");
				d("advTitle", "title");
				d("advContentType", "type");
				CKEDITOR.plugins.tooltip2.synAnchorSelector ?
					b.adv.advCSSClasses = v(g) : d("advCSSClasses", "class");
				d("advCharset", "charset");
				d("advStyles", "style");
				d("advRel", "rel")
			}
			d = b.anchors = [];
			var h;
			if (CKEDITOR.plugins.tooltip2.emptyAnchorFix) {
				f = a.document.getElementsByTag("a");
				c = 0;
				for (e = f.count(); c < e; c++)
					if (h = f.getItem(c), h.data("cke-saved-name") || h.hasAttribute("name")) d.push({
						name: h.data("cke-saved-name") || h.getAttribute("name"),
						id: h.getAttribute("id")
					})
			} else {
				f = new CKEDITOR.dom.nodeList(a.document.$.anchors);
				c = 0;
				for (e = f.count(); c < e; c++) h = f.getItem(c), d[c] = {
					name: h.getAttribute("name"),
					id: h.getAttribute("id")
				}
			}
			if (CKEDITOR.plugins.tooltip2.fakeAnchor) {
				f = a.document.getElementsByTag("img");
				c = 0;
				for (e = f.count(); c < e; c++)(h = CKEDITOR.plugins.tooltip2.tryRestoreFakeAnchor(a, f.getItem(c))) && d.push({
					name: h.getAttribute("name"),
					id: h.getAttribute("id")
				})
			}
			this._.selectedElement = g;
			return b
		},
		m = function(a) {
			a.adv || (a.adv = {});
			a.adv[this.id] = this.getValue() || ""
		},
		o = n.config.emailProtection || "";
	o && "encode" != o && (q = p = void 0, o.replace(/^([^(]+)\(([^)]+)\)$/, function(a, b, c) {
		q = b;
		p = [];
		c.replace(/[^,\s]+/g, function(a) {
			p.push(a)
		})
	}));
	var i = n.lang.common,
		b = n.lang.link;
	return {
		title: n.lang.tooltip2.title,
		minWidth: 1200,
		minHeight: 500,
		resizable: CKEDITOR.DIALOG_RESIZE_NONE,
		contents: [{
			id: "info",
			label: b.info,
			title: b.info,
			elements: [{
				type: "text",
				id: "url",
				label: i.url,
				required: !0,
				className: 'tooltip_id_'+n.id,
				onLoad: function () {
					this.allowOnChange = true
				},
				onKeyUp: function () {
				},
				onChange: function () {
				},
				validate: function () {
					var checked_id = 0;

					var iframe = document.getElementById('tooltip_iframe_'+n.id);

					var iframeDoc = iframe.contentWindow.document;
					var trs = iframeDoc.getElementsByClassName('x-grid-row');

					for (var i = 0; i < trs.length; i++) {
						var data = trs[i].innerHTML;
						if (data.indexOf('[+]')!='-1'){
							var divs = trs[i].getElementsByClassName('x-grid-cell-inner');
							checked_id = divs[0].innerText;
							divs[1].innerText = '';
						}
					}

					return true;

				},
				setup: function (a) {
					var iframe = document.getElementById('tooltip_iframe_'+n.id);

					if (typeof(a.url) != 'undefined' && typeof(a.url.url) != 'undefined') {
						iframe.src = '/oldadmin/?mode=tooltipBrowser&tooltip_id='+ a.url.url;
						this.allowOnChange = false;
						a.url && this.setValue(a.url.url);
						this.allowOnChange = true
					} else {
						iframe.src = '/oldadmin/?mode=tooltipBrowser&tooltip_id=0';
					}

				},
				commit: function (a) {
					this.onChange();
					if (!a.url) a.url = {};
					a.url.url = this.getValue();
					this.allowOnChange = false
				}
			},{
				type: 'html',
				html: html_block,
				id: 'content_block'
			}]
		}],
		onShow: function() {
			document.querySelectorAll('div.tooltip_id_'+n.id)[0].style.display = 'none';
			var a = this.getParentEditor(),
				b = a.getSelection(),
				c = null;
			(c = w.getSelectedLink(a)) && c.hasAttribute("tooltip_id") ? b.getSelectedElement() || b.selectElement(c) : c = null;
			this.setupContent(I.apply(this, [a, c]))
		},
		onOk: function() {

			var checked_id = 0;

			var iframe = document.getElementById('tooltip_iframe_'+n.id);

			var iframeDoc = iframe.contentWindow.document;
			var trs = iframeDoc.getElementsByClassName('x-grid-row');

			for (var i = 0; i < trs.length; i++) {
				var data = trs[i].innerHTML;
				if (data.indexOf('[+]')!='-1'){
					var divs = trs[i].getElementsByClassName('x-grid-cell-inner');
					checked_id = divs[0].innerText;
					divs[1].innerText = '';
				}
			}

			var a = {},
				b = [],
				c = {},
				d = this.getParentEditor();

			this.commitContent(c);

			var e = c.url && c.url.protocol != void 0 ? c.url.protocol : "",
				i = c.url && CKEDITOR.tools.trim(c.url.url) || "";

			e = d.getSelection();

			e = e.getRanges()[0];

			a.tooltip_id = checked_id;
			a.class = 'b-tooltip';

			delete a["data-cke-saved-href"];

			// Активация подсказки
            if ( a.tooltip_id ) {

                if (e.collapsed) {
                    d = new CKEDITOR.dom.text('Всплывающая подсказка');
                    e.insertNode(d);
                    e.selectNodeContents(d)
                }

                // Добавляем обвязку
                d = new CKEDITOR.style({
                    element: "span",
                    attributes: a
                });
                d.type = CKEDITOR.STYLE_INLINE;
                d.applyToRange(e);
                e.select();

            } else {

                var node = e.getTouchedStartNode();

                // Если выделенный фрагмент не является просто текстом и у него есть соответсвующая обвязка -> уберем её
                if ( (node.$.nodeType != 3) && (node.$.className.indexOf('b-tooltip') != -1) ){
                    // Деактивация подсказки. Удаляем обвязку c выделенного фрагмента
                    node.remove(true);
                    e.select();
				}

            }

		}
	}
});