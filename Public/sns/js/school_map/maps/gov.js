STK.register("kit.dom.parseDOM", function(a) {
	return function(c) {
		for ( var b in c) {
			if (c[b] && (c[b].length == 1)) {
				c[b] = c[b][0]
			}
		}
		return c
	}
});
STK.register("kit.extra.language", function(a) {
	window.$LANG || (window.$LANG = {});
	return function(b, c) {
		var d = a.core.util.language(b, $LANG);
		d = d.replace(/\\}/ig, "}");
		if (c) {
			d = a.templet(d, c)
		}
		return d
	}
});
STK.register("kit.extra.reuse", function(a) {
	return function(e, j) {
		var i, h, b;
		i = a.parseParam({}, j);
		b = [];
		var f = function() {
			var k = e();
			b.push({
				store : k,
				used : true
			});
			return k
		};
		var g = function(k) {
			a.foreach(b, function(m, l) {
				if (k === m.store) {
					m.used = true;
					return false
				}
			})
		};
		var d = function(k) {
			a.foreach(b, function(m, l) {
				if (k === m.store) {
					m.used = false;
					return false
				}
			})
		};
		var c = function() {
			for ( var l = 0, k = b.length; l < k; l += 1) {
				if (b[l]["used"] === false) {
					b[l]["used"] = true;
					return b[l]["store"]
				}
			}
			return f()
		};
		h = {};
		h.setUsed = g;
		h.setUnused = d;
		h.getOne = c;
		h.getLength = function() {
			return b.length
		};
		return h
	}
});
STK.register("module.layer", function(b) {
	var c = function(e) {
		var d = {};
		if (e.style.display == "none") {
			e.style.visibility = "hidden";
			e.style.display = "";
			d.w = e.offsetWidth;
			d.h = e.offsetHeight;
			e.style.display = "none";
			e.style.visibility = "visible"
		} else {
			d.w = e.offsetWidth;
			d.h = e.offsetHeight
		}
		return d
	};
	var a = function(g, f) {
		f = f || "topleft";
		var e = null;
		if (g.style.display == "none") {
			g.style.visibility = "hidden";
			g.style.display = "";
			e = b.core.dom.position(g);
			g.style.display = "none";
			g.style.visibility = "visible"
		} else {
			e = b.core.dom.position(g)
		}
		if (f !== "topleft") {
			var d = c(g);
			if (f === "topright") {
				e.l = e.l + d.w
			} else {
				if (f === "bottomleft") {
					e.t = e.t + d.h
				} else {
					if (f === "bottomright") {
						e.l = e.l + d.w;
						e.t = e.t + d.h
					}
				}
			}
		}
		return e
	};
	return function(h) {
		var k = b.core.dom.builder(h);
		var g = k.list.outer[0], e = k.list.inner[0];
		var j = b.core.dom.uniqueID(g);
		var i = {};
		var d = b.core.evt.custEvent.define(i, "show");
		b.core.evt.custEvent.define(d, "hide");
		var f = null;
		i.show = function() {
			g.style.display = "";
			b.core.evt.custEvent.fire(d, "show");
			return i
		};
		i.hide = function() {
			g.style.display = "none";
			b.custEvent.fire(d, "hide");
			return i
		};
		i.getPosition = function(l) {
			return a(g, l)
		};
		i.getSize = function(l) {
			if (l || !f) {
				f = c.apply(i, [ g ])
			}
			return f
		};
		i.html = function(l) {
			if (l !== undefined) {
				e.innerHTML = l
			}
			return e.innerHTML
		};
		i.text = function(l) {
			if (text !== undefined) {
				e.innerHTML = b.core.str.encodeHTML(l)
			}
			return b.core.str.decodeHTML(e.innerHTML)
		};
		i.appendChild = function(l) {
			e.appendChild(l);
			return i
		};
		i.getUniqueID = function() {
			return j
		};
		i.getOuter = function() {
			return g
		};
		i.getInner = function() {
			return e
		};
		i.getParentNode = function() {
			return g.parentNode
		};
		i.getDomList = function() {
			return k.list
		};
		i.getDomListByKey = function(l) {
			return k.list[l]
		};
		i.getDom = function(m, l) {
			if (!k.list[m]) {
				return false
			}
			return k.list[m][l || 0]
		};
		i.getCascadeDom = function(m, l) {
			if (!k.list[m]) {
				return false
			}
			return b.core.dom.cascadeNode(k.list[m][l || 0])
		};
		return i
	}
});
STK.register("module.dialog", function(a) {
	return function(n, o) {
		if (!n) {
			throw "module.dialog need template as first parameter"
		}
		var k, g, h, f, l, j, q, i, b, c, m, e;
		b = true;
		var d = function() {
			if (b !== false) {
				g.hide()
			}
		};
		var p = function() {
			k = a.parseParam({
				t : null,
				l : null,
				width : null,
				height : null
			}, o);
			g = a.module.layer(n, k);
			f = g.getOuter();
			l = g.getDom("title");
			i = g.getDom("title_content");
			j = g.getDom("inner");
			q = g.getDom("close");
			a.addEvent(q, "click", function() {
				m()
			});
			a.custEvent.add(g, "show", function() {
				a.hotKey.add(document.documentElement, [ "esc" ], d, {
					type : "keyup",
					disableInInput : true
				})
			});
			a.custEvent.add(g, "hide", function() {
				a.hotKey.remove(document.documentElement, [ "esc" ], d, {
					type : "keyup"
				});
				b = true
			})
		};
		p();
		e = a.objSup(g, [ "show", "hide" ]);
		m = function(r) {
			if (typeof c === "function" && !r) {
				if (c() === false) {
					return false
				}
			}
			e.hide();
			if (a.contains(document.body, g.getOuter())) {
				document.body.removeChild(g.getOuter())
			}
			return h
		};
		h = g;
		h.show = function() {
			if (!a.contains(document.body, g.getOuter())) {
				document.body.appendChild(g.getOuter())
			}
			e.show();
			return h
		};
		h.hide = m;
		h.setPosition = function(r) {
			f.style.top = r.t + "px";
			f.style.left = r.l + "px";
			return h
		};
		h.setMiddle = function() {
			var r = a.core.util.winSize();
			var s = g.getSize(true);
			f.style.top = a.core.util.scrollPos()["top"] + (r.height - s.h) / 2
					+ "px";
			f.style.left = (r.width - s.w) / 2 + "px";
			return h
		};
		h.setTitle = function(r) {
			i.innerHTML = r;
			return h
		};
		h.setContent = function(r) {
			if (typeof r === "string") {
				j.innerHTML = r
			} else {
				j.appendChild(r)
			}
			return h
		};
		h.clearContent = function() {
			while (j.children.length) {
				a.removeNode(j.children[0])
			}
			return h
		};
		h.setAlign = function() {
		};
		h.setBeforeHideFn = function(r) {
			c = r
		};
		h.clearBeforeHideFn = function() {
			c = null
		};
		h.unsupportEsc = function() {
			b = false
		};
		h.supportEsc = function() {
			b = true
		};
		return h
	}
});
STK.register("kit.dom.cssText", function(b) {
	var a = function(f, e) {
		var c = (f + ";" + e).replace(/(\s*(;)\s*)|(\s*(:)\s*)/g, "$2$4"), d;
		while (c && (d = c.match(/(^|;)([\w\-]+:)([^;]*);(.*;)?\2/i))) {
			c = c.replace(d[1] + d[2] + d[3], "")
		}
		return c
	};
	return function(d) {
		d = d || "";
		var e = [], c = {
			push : function(g, f) {
				e.push(g + ":" + f);
				return c
			},
			remove : function(g) {
				for ( var f = 0; f < e.length; f++) {
					if (e[f].indexOf(g + ":") == 0) {
						e.splice(f, 1)
					}
				}
				return c
			},
			getStyleList : function() {
				return e.slice()
			},
			getCss : function() {
				return a(d, e.join(";"))
			}
		};
		return c
	}
});
STK
		.register(
				"kit.dom.fix",
				function(d) {
					var a = !(d.core.util.browser.IE6 || (document.compatMode !== "CSS1Compat" && STK.IE)), b = /^(c)|(lt)|(lb)|(rt)|(rb)$/;
					function c(g) {
						return d.core.dom.getStyle(g, "display") != "none"
					}
					function e(h) {
						h = d.core.arr.isArray(h) ? h : [ 0, 0 ];
						for ( var g = 0; g < 2; g++) {
							if (typeof h[g] != "number") {
								h[g] = 0
							}
						}
						return h
					}
					function f(j, r, m) {
						if (!c(j)) {
							return
						}
						var q = "fixed", t, u, g, p, k = j.offsetWidth, l = j.offsetHeight, n = d.core.util
								.winSize(), o = 0, s = 0, h = d.kit.dom
								.cssText(j.style.cssText);
						if (!a) {
							q = "absolute";
							var i = d.core.util.scrollPos();
							o = t = i.top;
							s = u = i.left;
							switch (r) {
							case "lt":
								t += m[1];
								u += m[0];
								break;
							case "lb":
								t += n.height - l - m[1];
								u += m[0];
								break;
							case "rt":
								t += m[1];
								u += n.width - k - m[0];
								break;
							case "rb":
								t += n.height - l - m[1];
								u += n.width - k - m[0];
								break;
							case "c":
							default:
								t += (n.height - l) / 2 + m[1];
								u += (n.width - k) / 2 + m[0]
							}
							g = p = ""
						} else {
							t = p = m[1];
							u = g = m[0];
							switch (r) {
							case "lt":
								p = g = "";
								break;
							case "lb":
								t = g = "";
								break;
							case "rt":
								u = p = "";
								break;
							case "rb":
								t = u = "";
								break;
							case "c":
							default:
								t = (n.height - l) / 2 + m[1];
								u = (n.width - k) / 2 + m[0];
								p = g = ""
							}
						}
						if (r == "c") {
							if (t < o) {
								t = o
							}
							if (u < s) {
								u = s
							}
						}
						h.push("position", q).push("top", t + "px").push(
								"left", u + "px").push("right", g + "px").push(
								"bottom", p + "px");
						j.style.cssText = h.getCss()
					}
					return function(h, n, i) {
						var j, o, k = true, g;
						if (d.core.dom.isNode(h) && b.test(n)) {
							var l = {
								getNode : function() {
									return h
								},
								isFixed : function() {
									return k
								},
								setFixed : function(p) {
									(k = !!p) && f(h, j, o);
									return this
								},
								setAlign : function(p, q) {
									if (b.test(p)) {
										j = p;
										o = e(q);
										k && f(h, j, o)
									}
									return this
								},
								destroy : function() {
									if (!a) {
										a
												&& d.core.evt.removeEvent(
														window, "scroll", m)
									}
									d.core.evt.removeEvent(window, "resize", m);
									d.core.evt.custEvent.undefine(g)
								}
							};
							g = d.core.evt.custEvent.define(l, "beforeFix");
							l.setAlign(n, i);
							function m(p) {
								p = p || window.event;
								d.core.evt.custEvent.fire(g, "beforeFix",
										p.type);
								if (k && (!a || j == "c")) {
									f(h, j, o)
								}
							}
							if (!a) {
								d.core.evt.addEvent(window, "scroll", m)
							}
							d.core.evt.addEvent(window, "resize", m);
							return l
						}
					}
				});
STK
		.register(
				"module.mask",
				function(e) {
					var k, c = [], b, j = false, a = "STK-Mask-Key";
					var g = e.core.dom.setStyle, i = e.core.dom.getStyle, h = e.core.evt.custEvent;
					function d() {
						k = e.C("div");
						var m = '<div node-type="outer">';
						if (e.core.util.browser.IE6) {
							m += '<div style="position:absolute;width:100%;height:100%;"></div>'
						}
						m += "</div>";
						k = e.builder(m).list.outer[0];
						document.body.appendChild(k);
						j = true;
						b = e.kit.dom.fix(k, "lt");
						var n = function() {
							var o = e.core.util.winSize();
							k.style.cssText = e.kit.dom
									.cssText(k.style.cssText).push("width",
											o.width + "px").push("height",
											o.height + "px").getCss()
						};
						h.add(b, "beforeFix", n);
						n()
					}
					function l(m) {
						var n;
						if (!(n = m.getAttribute(a))) {
							m.setAttribute(a, n = e.getUniqueKey())
						}
						return ">" + m.tagName.toLowerCase() + "[" + a + '="'
								+ n + '"]'
					}
					var f = {
						getNode : function() {
							return k
						},
						show : function(n, m) {
							if (j) {
								n = e.core.obj.parseParam({
									opacity : 0.3,
									background : "#000000"
								}, n);
								k.style.background = n.background;
								g(k, "opacity", n.opacity);
								k.style.display = "";
								b.setAlign("lt");
								m && m()
							} else {
								e.Ready(function() {
									d();
									f.show(n, m)
								})
							}
							return f
						},
						hide : function() {
							k.style.display = "none";
							nowIndex = undefined;
							c = [];
							return f
						},
						showUnderNode : function(n, m) {
							if (e.isNode(n)) {
								f.show(m, function() {
									g(k, "zIndex", i(n, "zIndex"));
									var p = l(n);
									var o = e.core.arr.indexOf(c, p);
									if (o != -1) {
										c.splice(o, 1)
									}
									c.push(p);
									e.core.dom.insertElement(n, k,
											"beforebegin")
								})
							}
							return f
						},
						back : function() {
							if (c.length < 1) {
								return f
							}
							var n, m;
							c.pop();
							if (c.length < 1) {
								f.hide()
							} else {
								if ((m = c[c.length - 1])
										&& (n = e.sizzle(m, document.body)[0])) {
									g(k, "zIndex", i(n, "zIndex"));
									e.core.dom.insertElement(n, k,
											"beforebegin")
								} else {
									f.back()
								}
							}
							return f
						},
						destroy : function() {
							h.remove(b);
							k.style.display = "none";
							lastNode = undefined;
							_cache = {}
						}
					};
					return f
				});
STK.register("kit.dom.drag", function(a) {
	return function(d, p) {
		var h, g, n, l, c, k, e, i;
		var q = function() {
			f();
			j()
		};
		var f = function() {
			h = a.parseParam({
				moveDom : d,
				perchStyle : "border:solid #999999 2px;",
				dragtype : "perch",
				actObj : {},
				pagePadding : 5
			}, p);
			n = h.moveDom;
			g = {};
			l = {};
			c = a.drag(d, {
				actObj : h.actObj
			});
			if (h.dragtype === "perch") {
				k = a.C("div");
				e = false;
				i = false;
				n = k
			}
			d.style.cursor = "move"
		};
		var j = function() {
			a.custEvent.add(h.actObj, "dragStart", m);
			a.custEvent.add(h.actObj, "dragEnd", b);
			a.custEvent.add(h.actObj, "draging", o)
		};
		var m = function(r, u) {
			document.body.style.cursor = "move";
			var t = a.core.util.pageSize()["page"];
			l = a.core.dom.position(h.moveDom);
			l.pageX = u.pageX;
			l.pageY = u.pageY;
			l.height = h.moveDom.offsetHeight;
			l.width = h.moveDom.offsetWidth;
			l.pageHeight = t.height;
			l.pageWidth = t.width;
			if (h.dragtype === "perch") {
				var s = [];
				s.push(h.perchStyle);
				s.push("position:absolute");
				s.push("z-index:" + (h.moveDom.style.zIndex + 10));
				s.push("width:" + h.moveDom.offsetWidth + "px");
				s.push("height:" + h.moveDom.offsetHeight + "px");
				s.push("left:" + l.l + "px");
				s.push("top:" + l.t + "px");
				k.style.cssText = s.join(";");
				i = true;
				setTimeout(function() {
					if (i) {
						document.body.appendChild(k);
						e = true
					}
				}, 100)
			}
			if (d.setCapture !== undefined) {
				d.setCapture()
			}
		};
		var b = function(r, s) {
			document.body.style.cursor = "auto";
			if (d.setCapture !== undefined) {
				d.releaseCapture()
			}
			if (h.dragtype === "perch") {
				i = false;
				h.moveDom.style.top = k.style.top;
				h.moveDom.style.left = k.style.left;
				if (e) {
					document.body.removeChild(k);
					e = false
				}
			}
		};
		var o = function(t, A) {
			var z = l.t + (A.pageY - l.pageY);
			var s = l.l + (A.pageX - l.pageX);
			var u = z + l.height;
			var v = s + l.width;
			var r = l.pageHeight - h.pagePadding;
			var w = l.pageWidth - h.pagePadding;
			if (u < r && z > 0) {
				n.style.top = z + "px"
			} else {
				if (z < 0) {
					n.style.top = "0px"
				}
				if (u >= r) {
					n.style.top = r - l.height + "px"
				}
			}
			if (v < w && s > 0) {
				n.style.left = s + "px"
			} else {
				if (s < 0) {
					n.style.left = "0px"
				}
				if (v >= w) {
					n.style.left = w - l.width + "px"
				}
			}
		};
		q();
		g.destroy = function() {
			document.body.style.cursor = "auto";
			if (typeof n.setCapture === "function") {
				n.releaseCapture()
			}
			if (h.dragtype === "perch") {
				i = false;
				if (e) {
					document.body.removeChild(k);
					e = false
				}
			}
			a.custEvent.remove(h.actObj, "dragStart", m);
			a.custEvent.remove(h.actObj, "dragEnd", b);
			a.custEvent.remove(h.actObj, "draging", o);
			if (c.destroy) {
				c.destroy()
			}
			h = null;
			n = null;
			l = null;
			c = null;
			k = null;
			e = null;
			i = null
		};
		g.getActObj = function() {
			return h.actObj
		};
		return g
	}
});
STK
		.register(
				"ui.dialog",
				function(d) {
					var c = '<div class="W_layer" node-type="outer" style="display:none;position:absolute;z-index:10001"><div class="bg"><table border="0" cellspacing="0" cellpadding="0"><tr><td><div class="content"><div class="title" node-type="title"><span node-type="title_content"></span></div><a href="javascript:void(0);" class="W_close" title="#L{关闭}" node-type="close"></a><div node-type="inner"></div></div></td></tr></table></div></div>';
					var e = d.kit.extra.language;
					var b = null;
					var a = function() {
						var g = d.module.dialog(e(c));
						d.custEvent.add(g, "show", function() {
							d.module.mask.showUnderNode(g.getOuter(), {
								opacity : 0
							})
						});
						d.custEvent.add(g, "hide", function() {
							d.module.mask.back();
							g.setMiddle()
						});
						d.kit.dom.drag(g.getDom("title"), {
							actObj : g,
							moveDom : g.getOuter()
						});
						g.destroy = function() {
							f(g);
							try {
								g.hide(true)
							} catch (h) {
							}
						};
						return g
					};
					var f = function(g) {
						g.setTitle("").clearContent();
						b.setUnused(g)
					};
					return function(g) {
						var h = d.parseParam({
							isHold : false
						}, g);
						var j = h.isHold;
						h = d.core.obj.cut(h, [ "isHold" ]);
						if (!b) {
							b = d.kit.extra.reuse(a)
						}
						var i = b.getOne();
						if (!j) {
							d.custEvent.add(i, "hide",
									function() {
										d.custEvent.remove(i, "hide",
												arguments.callee);
										f(i)
									})
						}
						return i
					}
				});
STK
		.register(
				"ui.alert",
				function(d) {
					var c = '<div node-type="outer" class="layer_point"><dl class="point clearfix"><dt><span class="" node-type="icon"></span></dt><dd node-type="inner"><p class="W_texta" node-type="textLarge"></p><p class="W_textb" node-type="textSmall"></p></dd></dl><div class="btn"><a href="javascript:void(0)" class="W_btn_b" node-type="OK"></a></div></div>';
					var f = {
						success : "icon_succM",
						error : "icon_errorM",
						warn : "icon_warnM",
						"delete" : "icon_delM",
						question : "icon_questionM"
					};
					var e = d.kit.extra.language;
					var b = null;
					var a = function(h, g) {
						h.getDom("icon").className = g.icon;
						h.getDom("textLarge").innerHTML = g.textLarge;
						h.getDom("textSmall").innerHTML = g.textSmall;
						h.getDom("OK").innerHTML = "<span>" + g.OKText
								+ "</span>"
					};
					return function(l, h) {
						var i, j, k, m, g;
						i = d.parseParam({
							title : e("#L{提示}"),
							icon : "warn",
							textLarge : l,
							textSmall : "",
							OK : d.funcEmpty,
							OKText : e("#L{确定}"),
							timeout : 0
						}, h);
						i.icon = f[i.icon];
						j = {};
						if (!b) {
							b = d.kit.extra.reuse(function() {
								var n = d.module.layer(e(c));
								return n
							})
						}
						k = b.getOne();
						m = d.ui.dialog();
						m.setContent(k.getOuter());
						m.setTitle(i.title);
						a(k, i);
						d.addEvent(k.getDom("OK"), "click", m.hide);
						d.custEvent.add(m, "hide", function() {
							d.custEvent.remove(m, "hide", arguments.callee);
							d.removeEvent(k.getDom("OK"), "click", m.hide);
							b.setUnused(k);
							clearTimeout(g);
							i.OK()
						});
						if (i.timeout) {
							g = setTimeout(m.hide, i.timeout)
						}
						m.show().setMiddle();
						j.alt = k;
						j.dia = m;
						return j
					}
				});
STK.register("kit.dom.isTurnoff", function(a) {
	return function(b) {
		return !(b.parentNode && b.parentNode.nodeType != 11 && !b.disabled)
	}
});
STK.register("kit.extra.textareaUtils", function(c) {
	var a = {}, b = document.selection;
	a.selectionStart = function(f) {
		if (!b) {
			try {
				return f.selectionStart
			} catch (j) {
				return 0
			}
		}
		var k = b.createRange(), i, d, h = 0;
		var g = document.body.createTextRange();
		g.moveToElementText(f);
		for (h; g.compareEndPoints("StartToStart", k) < 0; h++) {
			g.moveStart("character", 1)
		}
		return h
	};
	a.selectionBefore = function(d) {
		return d.value.slice(0, a.selectionStart(d))
	};
	a.selectText = function(d, e, f) {
		d.focus();
		if (!b) {
			d.setSelectionRange(e, f);
			return
		}
		var g = d.createTextRange();
		g.collapse(1);
		g.moveStart("character", e);
		g.moveEnd("character", f - e);
		g.select()
	};
	a.insertText = function(f, e, h, g) {
		f.focus();
		g = g || 0;
		if (!b) {
			var i = f.value, k = h - g, d = k + e.length;
			f.value = i.slice(0, k) + e + i.slice(h, i.length);
			a.selectText(f, d, d);
			return
		}
		var j = b.createRange();
		j.moveStart("character", -g);
		j.text = e
	};
	a.replaceText = function(g, e) {
		g.focus();
		var h = g.value;
		var k = a.getSelectedText(g);
		var f = k.length;
		if (k.length == 0) {
			a.insertText(g, e, a.getCursorPos(g))
		} else {
			var j = a.getCursorPos(g);
			if (b) {
				var i = b.createRange();
				i.text = e;
				a.setCursor(g, j + e.length)
			} else {
				var d = j + k.length;
				g.value = h.slice(0, j) + e + h.slice(j + f, h.length);
				a.setCursor(g, j + e.length);
				return
			}
		}
	};
	a.getCursorPos = function(g) {
		var f = 0;
		if (STK.core.util.browser.IE) {
			g.focus();
			var d = null;
			d = b.createRange();
			var e = d.duplicate();
			e.moveToElementText(g);
			e.setEndPoint("EndToEnd", d);
			g.selectionStart = e.text.length - d.text.length;
			g.selectionEnd = g.selectionStart + d.text.length;
			f = g.selectionStart
		} else {
			if (g.selectionStart || g.selectionStart == "0") {
				f = g.selectionStart
			}
		}
		return f
	};
	a.getSelectedText = function(e) {
		var f = "";
		var d = function(g) {
			if (g.selectionStart != undefined && g.selectionEnd != undefined) {
				return g.value.substring(g.selectionStart, g.selectionEnd)
			} else {
				return ""
			}
		};
		if (window.getSelection) {
			f = d(e)
		} else {
			f = b.createRange().text
		}
		return f
	};
	a.setCursor = function(f, g, e) {
		g = g == null ? f.value.length : g;
		e = e == null ? 0 : e;
		f.focus();
		if (f.createTextRange) {
			var d = f.createTextRange();
			d.move("character", g);
			d.moveEnd("character", e);
			d.select()
		} else {
			f.setSelectionRange(g, g + e)
		}
	};
	a.unCoverInsertText = function(g, i, f) {
		f = (f == null) ? {} : f;
		f.rcs = f.rcs == null ? g.value.length : f.rcs * 1;
		f.rccl = f.rccl == null ? 0 : f.rccl * 1;
		var h = g.value, d = h.slice(0, f.rcs), e = h.slice(f.rcs + f.rccl,
				h == "" ? 0 : h.length);
		g.value = d + i + e;
		this.setCursor(g, f.rcs + (i == null ? 0 : i.length))
	};
	return a
});
STK
		.register(
				"kit.extra.count",
				function(b) {
					function a(k) {
						var e = 41, m = 140, c = 20, f = k;
						var l = k
								.match(/http:\/\/[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)+([-A-Z0-9a-z_\$\.\+\!\*\(\)\/,:;@&=\?\~\#\%]*)*/gi)
								|| [];
						var d = 0;
						for ( var g = 0, j = l.length; g < j; g++) {
							var h = b.core.str.bLength(l[g]);
							if (/^(http:\/\/t.cn)/.test(l[g])) {
								continue
							} else {
								if (/^(http:\/\/)+(t.sina.com.cn|t.sina.cn)/
										.test(l[g])
										|| /^(http:\/\/)+(weibo.com|weibo.cn)/
												.test(l[g])) {
									d += h <= e ? h
											: (h <= m ? c : (h - m + c))
								} else {
									d += h <= m ? c : (h - m + c)
								}
							}
							f = f.replace(l[g], "")
						}
						var n = Math.ceil((d + b.core.str.bLength(f)) / 2);
						return n
					}
					return function(c) {
						c = c.replace(/\r\n/g, "\n");
						return num = a(c)
					}
				});
STK
		.register(
				"module.editor",
				function(e) {
					var c = e.core.evt.addEvent;
					var b = e.core.evt.custEvent;
					var a = e.core.dom.getStyle;
					var d = e.core.dom.setStyle;
					return function(u, l) {
						var n = {};
						var l = l;
						var t = {};
						var k = {
							reset : function() {
								t.textEl.value = "";
								e.core.evt.custEvent.fire(n, "changed");
								t.textEl.removeAttribute("extra")
							},
							delWords : function(z) {
								var y = k.getWords();
								if (y.indexOf(z) > -1) {
									t.textEl.value = "";
									w.textInput(y.replace(z, ""))
								} else {
									return false
								}
							},
							getWords : function() {
								return e.core.str.trim(t.textEl.value)
							},
							getExtra : function() {
								var z;
								var y = t.textEl.getAttribute("extra") || "";
								if (y != null) {
									z = e.core.str.trim(y)
								}
								return z
							},
							focus : function(A, y) {
								if (typeof A != "undefined") {
									e.kit.extra.textareaUtils.setCursor(
											t.textEl, A, y)
								} else {
									var z = t.textEl.value.length;
									e.kit.extra.textareaUtils.setCursor(
											t.textEl, z)
								}
								f.cacheCurPos()
							},
							blur : function() {
								t.textEl.blur()
							},
							addExtraInfo : function(y) {
								if (typeof y == "string") {
									t.textEl.setAttribute("extra", y)
								}
							},
							disableEditor : function(y) {
								if (y === true) {
									t.textEl.setAttribute("disabled",
											"disabled")
								} else {
									t.textEl.removeAttribute("disabled")
								}
							},
							getCurPos : function() {
								var y = t.textEl.getAttribute("range") || "0&0";
								return y.split("&")
							},
							count : function() {
								var y = (e.core.str.trim(t.textEl.value).length == 0) ? e.core.str
										.trim(t.textEl.value)
										: t.textEl.value;
								return e.kit.extra.count(y)
							}
						};
						var f = {
							textElFocus : function() {
								if (t.recommendTopic) {
									e.core.dom.setStyle(t.recommendTopic,
											"display", "none")
								}
								e.custEvent.fire(n, "focus");
								if (t.num) {
									e.core.dom.setStyle(t.num, "display",
											"block")
								}
								if (k.getWords() == l.tipText) {
									k.delWords(l.tipText)
								}
							},
							textElBlur : function() {
								setTimeout(function() {
									if (t.textEl.value.length === 0) {
										if (t.recommendTopic) {
											e.core.dom.setStyle(
													t.recommendTopic,
													"display", "block")
										}
										if (t.num && t.recommendTopic) {
											e.core.dom.setStyle(t.num,
													"display", "none")
										}
										e.custEvent.fire(n, "blur");
										if (typeof l.tipText != "undefined") {
											t.textEl.value = l.tipText
										}
									}
								}, 50)
							},
							cacheCurPos : function() {
								var z = e.kit.extra.textareaUtils
										.getSelectedText(t.textEl);
								var A = (z == "" || z == null) ? 0 : z.length;
								var B = e.kit.extra.textareaUtils
										.getCursorPos(t.textEl);
								var y = B + "&" + A;
								t.textEl.setAttribute("range", y)
							}
						};
						var w = {
							textChanged : function() {
								e.custEvent.fire(n, "keyUpCount")
							},
							textInput : function(z, B) {
								var A = k.getCurPos();
								var B = A[0];
								var y = A[1];
								if (k.getWords() == l.tipText && z != l.tipText) {
									k.delWords(l.tipText)
								}
								e.kit.extra.textareaUtils.unCoverInsertText(
										t.textEl, z, {
											rcs : A[0],
											rccl : A[1]
										});
								f.cacheCurPos();
								e.core.evt.custEvent.fire(n, "changed")
							}
						};
						var q = {};
						var g = function() {
							s();
							h()
						};
						var v = function() {
							m();
							o();
							p();
							i()
						};
						var i = function() {
							if (l.storeWords) {
								if (t.textEl.value.length == 0) {
									w.textInput(l.storeWords)
								}
								return
							}
							if (l.tipText) {
								t.textEl.value = l.tipText
							}
						};
						var s = function() {
							if (!u) {
								throw "node is not defined in module editor"
							}
						};
						var h = function() {
							var y = e.core.dom.builder(u).list;
							t = e.kit.dom.parseDOM(y)
						};
						var m = function() {
							e.core.evt.addEvent(t.textEl, "focus",
									f.textElFocus);
							e.core.evt.addEvent(t.textEl, "blur", f.textElBlur);
							e.core.evt.addEvent(t.textEl, "mouseup",
									f.cacheCurPos);
							e.core.evt.addEvent(t.textEl, "keyup",
									f.cacheCurPos)
						};
						var j = function() {
							e.core.evt.custEvent.define(n, "changed")
						};
						var o = function() {
							j();
							e.core.evt.custEvent.add(n, "changed",
									w.textChanged)
						};
						var p = function() {
						};
						var x = function() {
						};
						g();
						var r = {
							reset : k.reset,
							getWords : k.getWords,
							getExtra : k.getExtra,
							delWords : k.delWords,
							focus : k.focus,
							blur : k.blur,
							insertText : w.textInput,
							check : w.textChanged,
							addExtraInfo : k.addExtraInfo,
							disableEditor : k.disableEditor,
							getCurPos : k.getCurPos,
							count : k.count
						};
						n.destroy = x;
						n.API = r;
						n.nodeList = t;
						n.init = v;
						n.opts = l;
						return n
					}
				});
STK
		.register(
				"common.editor.plugin.count",
				function(e) {
					var b;
					function d(n) {
						var h = 41, p = 140, f = 20, j = n;
						var o = n
								.match(/(http|https):\/\/[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)+([-A-Z0-9a-z_\$\.\+\!\*\(\)\/\,\:;@&=\?~#%]*)*/gi)
								|| [];
						var g = 0;
						for ( var k = 0, m = o.length; k < m; k++) {
							var l = e.core.str.bLength(o[k]);
							if (/^(http:\/\/t.cn)/.test(o[k])) {
								continue
							} else {
								if (/^(http:\/\/)+(t.sina.com.cn|t.sina.cn)/
										.test(o[k])
										|| /^(http:\/\/)+(weibo.com|weibo.cn)/
												.test(o[k])) {
									g += l <= h ? l
											: (l <= p ? f : (l - p + f))
								} else {
									g += l <= p ? f : (l - p + f)
								}
							}
							j = j.replace(o[k], "")
						}
						var q = Math.ceil((g + e.core.str.bLength(j)) / 2);
						return q
					}
					function a(i) {
						var h = d(i);
						var g = Math.abs(b - h);
						var f;
						if (h > b || h < 1) {
							f = {
								wordsnum : h,
								vnum : g,
								overflow : true
							}
						} else {
							if (h == 0) {
								f = {
									wordsnum : h,
									vnum : g,
									overflow : true
								}
							} else {
								f = {
									wordsnum : h,
									vnum : g,
									overflow : false
								}
							}
						}
						return f
					}
					function c(f, g) {
						if (!f.textEl) {
							throw "[editor plugin count]: plz check nodeList"
						}
					}
					return function(i) {
						var f = i.nodeList;
						var g;
						var l = i.opts;
						var k = e.kit.extra.language;
						b = l.limitNum;
						c(f);
						e.core.evt.custEvent.define(i, "textNum");
						e.custEvent.define(i, "keyUpCount");
						var m = f.textEl;
						var h = f.num;
						e.addEvent(m, "focus", function() {
							g = setInterval(function() {
								j()
							}, 200)
						});
						e.addEvent(m, "blur", function() {
							clearInterval(g)
						});
						var j = function() {
							var o = (e.core.str.trim(m.value).length == 0) ? e.core.str
									.trim(m.value)
									: m.value;
							o = o.replace(/\r\n/g, "\n");
							var n = a(o, l.limitNum);
							if (o.length >= 0 && m.focus) {
								if (n.overflow && n.wordsnum != 0) {
									h.innerHTML = k("#L{已经超过}")
											+ '<span class="W_error"> '
											+ n.vnum + "</span> " + k("#L{字}")
								} else {
									h.innerHTML = k("#L{还可以输入}<span>" + n.vnum
											+ "</span> #L{字}")
								}
							} else {
								if (o.length === 0) {
									h.innerHTML = "还可以输入<span>" + n.vnum
											+ "</span> 字"
								}
							}
							e.core.evt.custEvent.fire(i, "textNum", {
								count : n.wordsnum,
								isOver : n.overflow
							})
						};
						STK.core.evt.addEvent(m, "keyup", j);
						e.custEvent.add(i, "keyUpCount", j)
					}
				});
STK.register("common.listener", function(b) {
	var c = {};
	var a = {};
	a.define = function(e, f) {
		if (c[e] != null) {
			throw "common.listener.define: 频道已被占用"
		}
		c[e] = f;
		var d = {};
		d.register = function(h, g) {
			if (c[e] == null) {
				throw "common.listener.define: 频道未定义"
			}
			b.listener.register(e, h, g)
		};
		d.fire = function(g, h) {
			if (c[e] == null) {
				throw "commonlistener.define: 频道未定义"
			}
			b.listener.fire(e, g, h)
		};
		d.remove = function(h, g) {
			b.listener.remove(e, h, g)
		};
		d.cache = function(g) {
			return b.listener.cache(e, g)
		};
		return d
	};
	return a
});
STK
		.register(
				"module.at",
				function(g) {
					var n = window, j = document, k = g.core.util.browser, b = "font-family:Tahoma,宋体;", m = g.kit.extra.textareaUtils.selectionStart;
					var o;
					var c, h, e;
					var f = (function() {
						var d = {
							"<" : "&lt;",
							">" : "&gt;",
							'"' : "&quot;",
							"\\" : "&#92;",
							"&" : "&amp;",
							"'" : "&#039;",
							"\r" : "",
							"\n" : "<br>",
							" " : (navigator.userAgent
									.match(/.+(?:ie) ([\d.]+)/i) || [ 8 ])[1] < 8 ? [
									'<pre style="overflow:hidden;display:inline;',
									b, 'word-wrap:break-word;"> </pre>' ]
									.join("")
									: [ '<span style="white-space:pre-wrap;',
											b, '"> </span>' ].join("")
						};
						return function(q) {
							var p = q.replace(/(<|>|\"|\\|&|\'|\n|\r| )/g,
									function(r) {
										return d[r]
									});
							return p
						}
					})();
					var a = function() {
						var d = [], q = o.textEl.style.cssText, p;
						g
								.foreach(
										[ "margin", "padding", "border" ],
										function(r) {
											g
													.foreach(
															[ "Top", "Left",
																	"Bottom",
																	"Right" ],
															function(t) {
																var s;
																if (r != "border") {
																	s = d
																			.push(
																					r,
																					"-",
																					t
																							.toLowerCase(),
																					":",
																					g
																							.getStyle(
																									o.textEl,
																									r
																											+ t),
																					";");
																	return
																}
																g
																		.foreach(
																				[
																						"Color",
																						"Style",
																						"Width" ],
																				function(
																						u) {
																					d
																							.push(
																									r,
																									"-",
																									t
																											.toLowerCase(),
																									"-",
																									u
																											.toLowerCase(),
																									":",
																									g
																											.getStyle(
																													o.textEl,
																													[
																															r,
																															t,
																															u ]
																															.join("")),
																									";")
																				})
															})
										});
						d.push("font-size:" + g.getStyle(o.textEl, "fontSize")
								+ ";");
						return g.kit.dom
								.cssText(
										[
												q,
												d.join(""),
												b,
												"			word-wrap: break-word;			line-height: 18px;			overflow-y:auto;			overflow-x:hidden;			outline:none;		" ]
												.join("")).getCss()
					};
					var l = (function() {
						var u = g.builder([ '<div node-type="wrap">',
								'<span node-type="before"></span>',
								'<span node-type="flag"></span>',
								'<span node-type="after"></span>', "</div>" ]
								.join("")).list;
						var p = u.wrap[0], v = u.flag[0], d = u.after[0], x = u.before[0], t = 0, r, q, w;
						var s = function(y) {
							if (k.MOZ) {
								return -2
							}
							if (k.MOBILE && k.SAFARI
									&& (k.IPAD || k.ITOUCH || k.IPHONE)) {
								return -2
							}
							return 0
						};
						return {
							bind : function() {
								if (q === o.textEl) {
									return
								}
								var A = g.position(o.textEl);
								var y = [ "left:", A.l, "px;top:", A.t + 20,
										"px;" ].join("");
								q = o.textEl;
								var z = a();
								q.style.cssText = z;
								w = [
										y,
										z,
										"					position:absolute;					filter:alpha(opacity=0);					opacity:0;					z-index:-1000;				" ]
										.join("");
								p.style.cssText = w;
								if (!t) {
									t = 1;
									j.body.appendChild(p)
								}
							},
							content : function(y, A, B, z) {
								p.style.cssText = [
										w,
										"					width:",
										((parseInt(g.getStyle(q, "width")) || q.offsetWidth) + s()),
										"px;					height:",
										((parseInt(g.getStyle(q, "height")) || q.offsetHeight)),
										"px;					overflow-x:hidden;					overflow-y:",
										(/webkit/i.test(navigator.userAgent)) ? "hidden"
												: g.getStyle(q, "overflowY"),
										";				" ].join("");
								x.innerHTML = f(y);
								v.innerHTML = f(A) || "&thinsp;";
								d.innerHTML = f([ B, z ].join(""));
								clearTimeout(r);
								r = setTimeout(function() {
									var C = g.position(v);
									g.custEvent.fire(o.eId, "at", {
										t : C.t - q.scrollTop,
										l : C.l,
										key : B,
										flag : A,
										textarea : o.textEl
									})
								}, 30)
							}
						}
					})();
					var i = function() {
						if (g.kit.dom.isTurnoff(o.textEl)) {
							clearInterval(c);
							return
						}
						var d = o.textEl.value.replace(/\r/g, "");
						var s = m(o.textEl);
						if (s < 0 || s == e) {
							return
						}
						e = s;
						var q = d.slice(0, s);
						var p = q.match(new RegExp([ "(", o.flag,
								")([a-zA-Z0-9\u4e00-\u9fa5_]{0,20})$" ]
								.join("")));
						if (!p) {
							g.custEvent.fire(o.eId, "hidden");
							return
						}
						var r = d.slice(s);
						q = q.slice(0, -p[0].length);
						l.content(q, p[1], p[2], r)
					};
					return function(p) {
						if (!p || !p.textEl) {
							return
						}
						p = g.parseParam({
							textEl : null,
							flag : "@",
							eId : g.custEvent.define({}, [ "at", "hidden" ])
						}, p);
						var q = function() {
							clearInterval(c);
							g.removeEvent(p.textEl, "blur", q)
						};
						var d = function() {
							q();
							o = p;
							e = null;
							l.bind();
							c = setInterval(i, 200);
							g.addEvent(p.textEl, "blur", q)
						};
						g.addEvent(p.textEl, "focus", d);
						return p.eId
					}
				});
STK.register("kit.extra.merge", function(a) {
	return function(d, c) {
		var f = {};
		for ( var e in d) {
			f[e] = d[e]
		}
		for ( var e in c) {
			f[e] = c[e]
		}
		return f
	}
});
STK.register("kit.io.ajax", function(a) {
	return function(i) {
		var h, f, e, g, j, b, c;
		b = function(l) {
			j = false;
			i.onComplete(l, h.args);
			setTimeout(k, 0)
		};
		c = function(l) {
			j = false;
			i.onFail(l, h.args);
			setTimeout(k, 0)
		};
		e = [];
		g = null;
		j = false;
		h = a.parseParam({
			url : "",
			method : "get",
			responseType : "json",
			timeout : 30 * 1000,
			onTraning : a.funcEmpty,
			isEncode : true
		}, i);
		h.onComplete = b;
		h.onFail = c;
		var k = function() {
			if (!e.length) {
				return
			}
			if (j === true) {
				return
			}
			j = true;
			h.args = e.shift();
			g = a.ajax(h)
		};
		var d = function(m) {
			while (e.length) {
				e.shift()
			}
			j = false;
			if (g) {
				try {
					g.abort()
				} catch (l) {
				}
			}
			g = null
		};
		f = {};
		f.request = function(l) {
			if (!l) {
				l = {}
			}
			if (i.noQueue) {
				d()
			}
			if (!i.uniqueRequest || !g) {
				e.push(l);
				l._t = 0;
				k()
			}
		};
		f.abort = d;
		return f
	}
});
STK.register("kit.io.jsonp", function(a) {
	return function(d) {
		var c, f, b, h, e;
		c = a.parseParam({
			url : "",
			method : "get",
			responseType : "json",
			varkey : "_v",
			timeout : 30 * 1000,
			onComplete : a.funcEmpty,
			onTraning : a.funcEmpty,
			onFail : a.funcEmpty,
			isEncode : true
		}, d);
		b = [];
		h = {};
		e = false;
		var g = function() {
			if (!b.length) {
				return
			}
			if (e === true) {
				return
			}
			e = true;
			h.args = b.shift();
			h.onComplete = function(i) {
				e = false;
				c.onComplete(i, h.args);
				setTimeout(g, 0)
			};
			h.onFail = function(i) {
				e = false;
				c.onFail(i);
				setTimeout(g, 0)
			};
			a.jsonp(a.kit.extra.merge(c, {
				args : h.args,
				onComplete : function(i) {
					h.onComplete(i)
				},
				onFail : function(i) {
					try {
						h.onFail(i)
					} catch (j) {
					}
				}
			}))
		};
		f = {};
		f.request = function(i) {
			if (!i) {
				i = {}
			}
			b.push(i);
			i._t = 1;
			g()
		};
		f.abort = function(i) {
			while (b.length) {
				b.shift()
			}
			e = false;
			h = null
		};
		return f
	}
});
STK.register("kit.io.inter", function(a) {
	return function() {
		var b, d, c;
		b = {};
		d = {};
		c = {};
		b.register = function(f, e) {
			if (d[f] !== undefined) {
				throw f + " interface has been registered"
			}
			d[f] = e;
			c[f] = {}
		};
		b.hookComplete = function(e, g) {
			var f = a.core.util.getUniqueKey();
			c[e][f] = g;
			return f
		};
		b.removeHook = function(e, f) {
			if (c[e] && c[e][f]) {
				delete c[e][f]
			}
		};
		b.getTrans = function(g, e) {
			var f = a.kit.extra.merge(d[g], e);
			f.onComplete = function(i, l) {
				try {
					e.onComplete(i, l)
				} catch (j) {
				}
				if (i.code === "100000" || i.code === "A00006") {
					try {
						e.onSuccess(i, l)
					} catch (j) {
					}
				} else {
					try {
						if (i.code === "100002") {
							window.location.href = i.data;
							return
						}
						e.onError(i, l)
					} catch (j) {
					}
				}
				for ( var h in c[g]) {
					try {
						c[g][h](i, l)
					} catch (j) {
					}
				}
			};
			if (d[g]["requestMode"] === "jsonp") {
				return a.kit.io.jsonp(f)
			} else {
				if (d[g]["requestMode"] === "ijax") {
					return a.kit.io.ijax(f)
				} else {
					return a.kit.io.ajax(f)
				}
			}
		};
		b.request = function(h, e, g) {
			var f = a.core.json.merge(d[h], e);
			f.onComplete = function(j, m) {
				try {
					e.onComplete(j, m)
				} catch (l) {
				}
				if (j.code === "100000" || j.code === "A00006") {
					try {
						e.onSuccess(j, m)
					} catch (l) {
					}
				} else {
					try {
						if (j.code === "100002") {
							window.location.href = j.data;
							return
						}
						e.onError(j, m)
					} catch (l) {
					}
				}
				for ( var i in c[h]) {
					try {
						c[h][i](j, m)
					} catch (l) {
					}
				}
			};
			f = a.core.obj.cut(f, [ "noqueue" ]);
			f.args = g;
			if (d[h]["requestMode"] === "jsonp") {
				return a.jsonp(f)
			} else {
				if (d[h]["requestMode"] === "ijax") {
					return a.ijax(f)
				} else {
					return a.ajax(f)
				}
			}
		};
		return b
	}
});
STK.register("common.trans.global", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("language", {
		url : "/aj/user/lang",
		method : "post"
	});
	b("followList", {
		url : "/aj/mblog/attention"
	});
	return a
});
STK
		.register(
				"module.suggest",
				function(j) {
					var h = null, e = j.custEvent, l = e.define, f = e.fire, o = e.add, g = j.addEvent, c = j.removeEvent, m = j.stopEvent;
					var b = [], a = {};
					var i = {
						ENTER : 13,
						ESC : 27,
						UP : 38,
						DOWN : 40,
						TAB : 9
					};
					var k = function(v) {
						var u = -1, s = [], q = v.textNode, r = v.uiNode;
						var z = j.core.evt.delegatedEvent(r);
						var p = l(q, [ "open", "close", "indexChange",
								"onSelect", "onIndexChange", "onClose",
								"onOpen" ]);
						var x = function() {
							return j.sizzle([ "[action-type=", v.actionType,
									"]" ].join(""), r)
						};
						var y = function() {
							u = -1;
							c(q, "keydown", t);
							z.destroy()
						};
						var t = function(C) {
							var D, B;
							if (!(D = C) || !(B = D.keyCode)) {
								return
							}
							if (B == i.ENTER) {
								m();
								f(p, "onSelect", [ u, q ]);
								return false
							}
							if (B == i.UP) {
								m();
								var A = x().length;
								u = u < 1 ? A - 1 : u - 1;
								f(p, "onIndexChange", [ u ]);
								return false
							}
							if (B == i.DOWN) {
								m();
								var A = x().length;
								u = u == (A - 1) ? 0 : u + 1;
								f(p, "onIndexChange", [ u ]);
								return false
							}
							if (B == i.ESC) {
								m();
								y();
								f(p, "onClose");
								return false
							}
							if (B == i.TAB) {
								y();
								f(p, "onClose");
								return false
							}
						};
						var w = function(A) {
							f(p, "onSelect",
									[ j.core.arr.indexOf(A.el, x()), q ])
						};
						var n = function(A) {
							u = j.core.arr.indexOf(A.el, x());
							f(p, "onIndexChange", [ j.core.arr.indexOf(A.el,
									x()) ])
						};
						o(p, "open", function(B, A) {
							q = A;
							y();
							g(A, "keydown", t);
							z.add(v.actionType, "mouseover", n);
							z.add(v.actionType, "click", w);
							f(p, "onOpen")
						});
						o(p, "close", function() {
							y();
							f(p, "onClose")
						});
						o(p, "indexChange", function(A, B) {
							u = B;
							f(p, "onIndexChange", [ u ])
						});
						return p
					};
					var d = function(n) {
						var p = n.textNode;
						var q = j.core.arr.indexOf(p, b);
						if (!a[q]) {
							b[q = b.length] = p;
							a[q] = k(n)
						}
						return a[q]
					};
					return function(n) {
						if (!n.textNode || !n.uiNode) {
							return
						}
						n = j.parseParam({
							textNode : h,
							uiNode : h,
							actionType : "item",
							actionData : "index"
						}, n);
						return d(n)
					}
				});
STK.register("common.channel.at", function(b) {
	var a = [ "open", "close" ];
	return b.common.listener.define("common.channel.at", a)
});
STK
		.register(
				"common.editor.plugin.at",
				function(e) {
					var j = '<div style="" class="layer_menu_list"><ul node-type="suggestWrap"></ul></div>';
					var l = '<#et temp data><li class="title">想用@提到谁？</li><#list data as list><li action-type="item" <#if (list_index == 0)>class="cur" </#if>action-data="value=${list.screen_name}" value="${list.screen_name}"><a href="#">${list.screen_name}<#if (list.remark)>(${list.remark})</#if></a></li></#list></#et>';
					var f, i, r, c, n, s, h = false, k;
					var q;
					var d = 0;
					var g = function() {
						setTimeout(function() {
							e.custEvent.fire(f, "close")
						}, 200)
					};
					var a = function() {
						n.style.display = "none"
					};
					var m = function() {
						e.custEvent.add(f, "onIndexChange", function(v, u) {
							o(u)
						});
						e.custEvent.add(f, "onSelect",
								function(x, w, v) {
									e.core.evt.stopEvent();
									var z = q[w].getAttribute("value");
									var u = s.length * 1;
									var y = e.kit.extra.textareaUtils
											.selectionStart(v) * 1;
									e.kit.extra.textareaUtils.insertText(v, z
											+ " ", y, u);
									e.custEvent.fire(f, "close")
								});
						e.addEvent(r.textEl, "blur", g);
						e.custEvent.add(f, "onClose", a);
						e.custEvent.add(f, "onOpen", function(v, u) {
							c.style.display = "";
							n.style.display = "";
							h = true;
							setTimeout(function() {
								e.custEvent.fire(f, "indexChange", 0)
							}, 100)
						})
					};
					var t = function() {
						STK.core.evt.custEvent.add(i, "hidden", function(u, v) {
							e.custEvent.fire(f, "close")
						});
						STK.core.evt.custEvent
								.add(
										i,
										"at",
										function(v, w) {
											s = w.key;
											if (s.length == 0) {
												e.custEvent.fire(f, "close");
												return
											}
											var u = e.common.trans.global
													.getTrans(
															"followList",
															{
																onSuccess : function(
																		y, B) {
																	if (!e.core.dom
																			.contains(
																					document.body,
																					n)) {
																		document.body
																				.appendChild(n)
																	}
																	var x = e.core.util
																			.easyTemplate(
																					l,
																					y.data);
																	e.custEvent
																			.fire(
																					f,
																					"open",
																					w.textarea);
																	var z = STK.core.dom
																			.builder(x);
																	var A = z.box;
																	c.innerHTML = A;
																	n.style.cssText = [
																			"z-index:11001;background-color:#ffffff;position:absolute;left:",
																			w.l,
																			"px;top:",
																			w.t,
																			"px;" ]
																			.join("");
																	e.common.channel.at
																			.fire(
																					"open",
																					"sdsdfsf")
																},
																onError : function() {
																	e.custEvent
																			.fire(
																					f,
																					"close")
																}
															}).request({
														q : s
													})
										})
					};
					var b = function() {
						k = r.textEl;
						i = STK.module.at({
							textEl : k
						})
					};
					var p = function(u) {
						n = STK.C("div");
						e.core.util.hideContainer.appendChild(n);
						if (n.innerHTML.length == 0) {
							n.innerHTML = j;
							c = e.core.dom.sizzle('[node-type="suggestWrap"]',
									n)[0]
						}
						f = e.module.suggest({
							textNode : u,
							uiNode : c,
							actionType : "item",
							actionData : "value"
						});
						m()
					};
					var o = function(u) {
						q = e.sizzle("li[class!=title]", c);
						e.core.dom.removeClassName(q[d], "cur");
						e.core.dom.addClassName(q[u], "cur");
						d = u
					};
					return function(v, u) {
						r = v.nodeList;
						var w = {};
						w.init = function() {
							b();
							p(r.textEl);
							t()
						};
						return w
					}
				});
STK.register("common.editor.plugin.hotKey", function(a) {
	return function(c) {
		var b = function() {
			a.common.extra.shine(c.nodeList.textEl);
			c.API.focus()
		};
		a.hotKey.add(document.documentElement, [ "f" ], b, {
			type : "keyup",
			disableInInput : true
		});
		a.hotKey.add(document.documentElement, [ "p" ], b, {
			type : "keyup",
			disableInInput : true
		})
	}
});
STK.register("common.editor.base", function(c) {
	var b = {
		limitNum : 140
	};
	function a() {
	}
	return function(h, d) {
		var i = {};
		var k, j, g, l;
		k = c.kit.extra.merge(b, d);
		j = c.module.editor(h, k);
		g = j.nodeList;
		l = [];
		if (typeof d.count == "undefined" || d.count == "enable") {
			var f = c.common.editor.plugin.count(j, k)
		}
		var e = c.common.editor.plugin.at(j, k);
		e.init();
		j.init();
		j.widget = function(n, o, m) {
			l.push(n);
			n.init(j, o, m);
			return j
		};
		j.closeWidget = function() {
			if (l && l.length != 0) {
				for ( var n = 0, m = l.length; n < m; n++) {
					l[n].hide()
				}
			}
		};
		c.common.editor.plugin.hotKey(j);
		return j
	}
});
STK.register("common.trans.message", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("delete", {
		url : "/aj/message/del",
		method : "post"
	});
	b("deleteUserMsg", {
		url : "/aj/message/destroy",
		method : "post"
	});
	b("create", {
		url : "/aj/message/add",
		method : "post"
	});
	b("createLite", {
		url : "/government/reg/aj_addmsg.php",
		method : "post"
	});
	b("search", {
		url : "/message",
		method : "get"
	});
	b("attachDel", {
		url : "/aj/message/attach/del",
		method : "get"
	});
	b("getDetail", {
		url : "/aj/message/detail",
		method : "get"
	});
	return a
});
STK.register("common.trans.validateCode", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("checkValidate", {
		url : "/aj/pincode/verified",
		method : "post"
	});
	return a
});
STK.register("kit.io.cssLoader", function(d) {
	var b = $CONFIG.version || "";
	var a = "http://img.t.sinajs.cn/t4/";
	if (typeof $CONFIG != "undefined") {
		a = $CONFIG.cssPath || a
	}
	var c = {};
	return function(f, k, h, i) {
		i = i || b;
		h = h || function() {
		};
		var n = function(r, p) {
			var q = c[r] || (c[r] = {
				loaded : false,
				list : []
			});
			if (q.loaded) {
				p(r);
				return false
			}
			q.list.push(p);
			if (q.list.length > 1) {
				return false
			}
			return true
		};
		var j = function(q) {
			var p = c[q].list;
			for ( var r = 0; r < p.length; r++) {
				p[r](q)
			}
			c[q].loaded = true;
			delete c[q].list
		};
		if (!n(f, h)) {
			return
		}
		var m = a + f + "?version=" + i;
		var l = d.C("link");
		l.setAttribute("rel", "Stylesheet");
		l.setAttribute("type", "text/css");
		l.setAttribute("charset", "utf-8");
		l.setAttribute("href", m);
		document.getElementsByTagName("head")[0].appendChild(l);
		var e = d.C("div");
		e.id = k;
		d.core.util.hideContainer.appendChild(e);
		var o = 3000;
		var g = function() {
			if (parseInt(d.core.dom.getStyle(e, "height")) == 42) {
				d.core.util.hideContainer.removeChild(e);
				j(f);
				return
			}
			if (--o > 0) {
				setTimeout(g, 10)
			} else {
				d.log(f + "timeout!");
				d.core.util.hideContainer.removeChild(e);
				delete c[f]
			}
		};
		setTimeout(g, 50)
	}
});
STK
		.register(
				"common.dialog.validateCode",
				function(g) {
					var b = window.$LANG, e = g.kit.extra.language;
					var h = "/aj/pincode/pin?type=rule&lang=" + $CONFIG.lang
							+ "&ts=";
					var c = {
						dialog_html : '<div class="layer_veriyfycode"><div class="v_image"><img height="50" width="450" class="yzm_img" /></div><p class="v_chng"><a href="#" onclick="return false;" class="yzm_change" action-type="yzm_change">#L{换另一组题目}</a></p><p class="v_ans_t">#L{请输入上面问题的答案}：</p><p class="v_ans_i"><input type="text" class="W_inputStp v_inp yzm_input ontext" action-type="yzm_input"/><div class="M_notice_del yzm_error" style="display:none;"><span class="icon_del"></span><span class="txt"></span></div></p><p class="v_btn"><a class="W_btn_b yzm_submit" href="#" action-type="yzm_submit"><span>#L{确定}</span></a><a class="W_btn_a yzm_cancel" href="#" action-type="yzm_cancel" action-data="value=frombtn"><span>#L{取消}</span></a></p></div>'
					};
					var f, d, a;
					return function() {
						if (f) {
							return f
						}
						g.kit.io.cssLoader(
								"style/css/module/layer/layer_verifycode.css",
								"js_style_css_module_layer_layer_verifycode");
						var q = {};
						var t = {};
						var w;
						var k;
						var v;
						var s;
						var p = function() {
							t.yzm_error.innerHTML = "";
							t.yzm_error.parentNode.style.display = "none"
						};
						var l = function(A) {
							t.yzm_error.innerHTML = A;
							t.yzm_error.parentNode.style.display = ""
						};
						var o = function() {
							if (!w) {
								x()
							}
							p();
							t.input_text.value = "";
							w.show();
							i.changesrc();
							w.setMiddle();
							g.hotKey.add(document.documentElement, [ "esc" ],
									i.closeDialog, {
										type : "keyup",
										disableInInput : true
									})
						};
						var x = function() {
							w = g.ui.dialog({
								isHold : true
							});
							w.setTitle(e("#L{请输入验证码}"));
							w.setContent(e(c.dialog_html));
							c = null;
							var A = w.getOuter();
							j(A);
							j = null;
							m();
							m = null
						};
						var n = g.common.trans.validateCode.getTrans(
								"checkValidate", {
									onError : function() {
										l(e("#L{验证码错误}"));
										i.changesrc();
										v = false
									},
									onFail : function() {
										l(e("#L{验证码错误}"));
										i.changesrc();
										v = false
									},
									onSuccess : function(C, E) {
										var A = C.data.retcode;
										p();
										t.input_text.value = "";
										w.hide();
										var B = k.requestAjax;
										var D = g.kit.extra.merge(k.param, {
											retcode : A
										});
										B.request(D);
										v = false
									}
								});
						var y = function() {
						};
						var u = function() {
						};
						var j = function(A) {
							t.vImg = g.core.dom.sizzle("img.yzm_img", A)[0];
							t.yzm_change = g.core.dom.sizzle("a.yzm_change", A)[0];
							t.yzm_submit = g.core.dom.sizzle("a.yzm_submit", A)[0];
							t.yzm_cancel = g.core.dom.sizzle("a.yzm_cancel", A)[0];
							t.input_text = g.core.dom.sizzle("input.yzm_input",
									A)[0];
							t.yzm_error = g.core.dom.sizzle(
									"div.yzm_error span.txt", A)[0];
							t.close_icon = w.getDom("close")
						};
						var i = {
							changesrc : function() {
								var B = h + g.getUniqueKey();
								t.vImg.setAttribute("src", B);
								try {
									t.yzm_change.blur()
								} catch (A) {
								}
							},
							checkValidateCode : function() {
								p();
								var A = g.core.str.trim(t.input_text.value);
								if (!A) {
									l(e("#L{请输入验证码}"))
								} else {
									if (!v) {
										v = true;
										n.request({
											secode : A
										})
									}
								}
								try {
									t.yzm_submit.blur()
								} catch (B) {
								}
							},
							closeDialog : function(B) {
								if (typeof B == "object" && B.el) {
									w.hide()
								}
								if (typeof k == "object" && k.onRelease
										&& typeof k.onRelease == "function") {
									k.onRelease()
								}
								g.hotKey.remove(document.documentElement,
										[ "esc" ], i.closeDialog, {
											type : "keyup"
										});
								try {
									g.preventDefault()
								} catch (A) {
								}
							},
							onFocus : function(B) {
								B = g.core.evt.getEvent();
								var A = B.target || B.srcElement;
								var C = A.value;
								if (!C) {
									p()
								}
							}
						};
						var m = function() {
							var A = w.getOuter();
							s = g.core.evt.delegatedEvent(A);
							s.add("yzm_change", "click", function() {
								i.changesrc();
								g.preventDefault()
							});
							s.add("yzm_submit", "click", function() {
								i.checkValidateCode();
								g.preventDefault()
							});
							s.add("yzm_cancel", "click", i.closeDialog);
							g.core.evt.addEvent(t.close_icon, "click",
									i.closeDialog);
							g.core.evt.addEvent(t.input_text, "focus",
									i.onFocus);
							g.core.evt
									.addEvent(t.input_text, "blur", i.onFocus)
						};
						var z = function() {
							if (d) {
								return
							}
							if (w) {
								s.destroy();
								g.core.evt.removeEvent(t.close_icon, "click",
										i.closeDialog);
								g.core.evt.removeEvent(t.input_text, "focus",
										i.onFocus);
								g.core.evt.removeEvent(t.input_text, "blur",
										i.onFocus);
								w && w.destroy && w.destroy()
							}
							d = true
						};
						var r = function(B, C, A) {
							g.kit.io
									.cssLoader(
											"style/css/module/layer/layer_verifycode.css",
											"js_style_css_module_layer_layer_verifycode",
											function() {
												if (B.code == "100027") {
													k = A;
													o()
												} else {
													if (B.code === "100000") {
														try {
															var D = A.onSuccess;
															D && D(B, C)
														} catch (E) {
														}
													} else {
														try {
															if (B.code === "100002") {
																window.location.href = B.data;
																return
															}
															var D = A.onError;
															D && D(B, C)
														} catch (E) {
														}
													}
												}
											})
						};
						u();
						u = null;
						q.destroy = z;
						q.validateIntercept = r;
						q.addUnloadEvent = function() {
							if (a) {
								return
							}
							if (w) {
								g.core.evt.addEvent(window, "unload", z)
							}
							a = true
						};
						f = q;
						return q
					}
				});
STK
		.register(
				"common.dialog.sendLiteMessage",
				function(a) {
					$L = a.kit.extra.language;
					return function(l) {
						var i, c, e, h, b, j, k, m, g = {};
						var d;
						var f = {
							DOM : {},
							objs : {
								trans : {
									messageCreate : a.common.trans.message
											.getTrans(
													"createLite",
													{
														onComplete : function(
																o, p) {
															var n = {
																onSuccess : function(
																		q) {
																	f.DOM.textEl.value = "";
																	f.DOM.textEl
																			.focus();
																	f
																			.unLockSubmit();
																	f.objs.dialog
																			.hide()
																},
																onError : function(
																		q) {
																	a.ui
																			.alert(q.msg);
																	f
																			.unLockSubmit()
																},
																onFail : function(
																		q) {
																	print_r("onFail");
																	f
																			.unLockSubmit()
																}
															};
															f.objs.validCodeLayer = a.common.dialog
																	.validateCode();
															f.objs.validCodeLayer
																	.validateIntercept(
																			o,
																			p,
																			n)
														}
													})
								}
							},
							DEventFun : {
								clickSubmitBtn : function(n) {
									if (!f.submitIsLock) {
										f.submitMessage()
									}
								}
							},
							submitMessage : function() {
								var n = a.htmlToJson(d);
								f.objs.trans.messageCreate.request(n);
								f.lockSubmit()
							},
							lockSubmit : function() {
								f.submitIsLock = true;
								a
										.addClassName(f.DOM.btnText,
												"W_btn_b_disable")
							},
							unLockSubmit : function() {
								f.submitIsLock = false;
								a.removeClassName(f.DOM.btnText,
										"W_btn_b_disable")
							},
							show : function() {
								f.objs.dialog._show();
								f.objs.dialog.setMiddle()
							},
							createDialogHtml : function() {
								if (l.nickname) {
									sInputHTML = l.nickname
											+ '<input type="hidden" name="uid" value="'
											+ l.uid
											+ '" /><input type="hidden" name="name" value="'
											+ l.nickname + '" />'
								} else {
									sInputHTML = '<input type="text" value="'
											+ l.uid + '" name="uid" />'
								}
								sDialogHTML = '<div class="W_private_letter"><table class="form_private"><tbody><tr><th>#L{发&nbsp;&nbsp;给：}</th><td>'
										+ sInputHTML
										+ '</td></tr><tr><th>#L{内&nbsp;&nbsp;容：}</th><td><p class="num" node-type="num">#L{还可以输入}<em>300</em>#L{字}</p><textarea class="W_no_outline" name="text" node-type="textEl"></textarea><div class="btn_s fr"><a class="W_btn_b" href="javascript:void(0);" node-type="btnText" action-type="submitBtn"><span>发送</span></a></div></td></tr></tbody></table></div>';
								return $L(sDialogHTML)
							}
						};
						i = function() {
							if (!l) {
								l = {}
							}
							l.title = l.tite || $L("#L{发私信}")
						};
						c = function() {
							if (!1) {
								throw new Error(
										"common.dialog.sendLiteMessage 必需的节点 不存在")
							}
						};
						e = function() {
							f.objs.dialog = a.ui.dialog({
								isHold : true
							});
							f.objs.dialog.setTitle(l.title);
							d = f.objs.dialog.getInner();
							d.innerHTML = f.createDialogHtml();
							f.DOM = a.kit.dom.parseDOM(a.builder(d).list);
							f.objs.editor = a.common.editor.base(d, {
								limitNum : 300
							});
							f.objs.dialog._show = f.objs.dialog.show;
							g = f.objs.dialog
						};
						h = function() {
							f.objs.DEvent = a.core.evt.delegatedEvent(d);
							f.objs.DEvent.add("submitBtn", "click",
									f.DEventFun.clickSubmitBtn)
						};
						b = function() {
						};
						j = function() {
						};
						k = function() {
							if (f) {
								a.foreach(f.objs.trans, function(n) {
									n.abort()
								});
								if (f.objs.dialog) {
									f.objs.dialog.hide()
								}
								a.foreach(f.objs, function(n) {
									if (n.destroy) {
										n.destroy()
									}
								});
								f = null
							}
						};
						m = function() {
							i();
							c();
							e();
							h();
							b();
							j()
						};
						m();
						g.destroy = k;
						g.show = f.show;
						return g
					}
				});
STK.register("module.tab", function(a) {
	return function(g) {
		var i, c, d, h, b, j, k, l, f = {};
		var e = {
			DOM : {},
			objs : {},
			DOM_eventFun : {},
			act : function(o) {
				var p, m, n = false;
				if (o.constructor === String) {
					o = parseInt(o, 10)
				}
				if (o.constructor !== Number) {
					for (p = 0, m = e.aTab.length; p < m; ++p) {
						if (e.aTab[p] === o) {
							o = p;
							n = true;
							break
						}
					}
					if (!n) {
						o = -1
					}
				}
				if (e.onChange(o) === false) {
					return false
				}
				e.actTab(o);
				e.actBox(o);
				e.index = o
			},
			actTab : function(n) {
				var o, m;
				for (o = 0, m = e.aTab.length; o < m; o++) {
					if (e.actTabClass) {
						a.removeClassName(e.aTab[o], e.actTabClass)
					}
					if (e.disTabClass) {
						a.addClassName(e.aTab[o], e.disTabClass)
					}
				}
				var p = e.aTab[n] || null;
				if (p !== null) {
					if (e.actTabClass) {
						a.addClassName(p, e.actTabClass)
					}
					if (e.disTabClass) {
						a.removeClassName(p, e.disTabClass)
					}
				}
			},
			actBox : function(p) {
				var q, n, o = arguments.callee;
				if (!o.hiddenBox) {
					o.hiddenBox = function(r) {
						if (r && r.nodeType === 1) {
							if (e.actBoxClass) {
								a.removeClassName(r, e.actBoxClass)
							}
							if (e.disBoxClass) {
								a.addClassName(r, e.disBoxClass)
							}
							if (e.displayNone) {
								r.style.display = "none"
							}
						}
					}
				}
				if (!o.showBox) {
					o.showBox = function(r) {
						if (r && r.nodeType === 1) {
							if (e.actBoxClass) {
								a.addClassName(r, e.actBoxClass)
							}
							if (e.disBoxClass) {
								a.removeClassName(r, e.disBoxClass)
							}
							if (e.styleDisplay) {
								r.style.display = e.styleDisplay
							} else {
								r.style.display = ""
							}
						}
					}
				}
				a.foreach(e.aBox, function(r) {
					if (r.constructor === Array) {
						a.foreach(r, function(s) {
							o.hiddenBox(s)
						})
					} else {
						o.hiddenBox(r)
					}
				});
				var m = e.aBox[p] || null;
				if (m !== null) {
					if (m.constructor === Array) {
						a.foreach(m, function(r) {
							o.showBox(r)
						})
					} else {
						o.showBox(m)
					}
				}
			}
		};
		i = function() {
		};
		c = function() {
		};
		d = function() {
			e.index = g.index || 0;
			e.indexTo = null;
			e.aTab = g.aTab || [];
			e.aBox = g.aBox || [];
			e.styleDisplay = g.styleDisplay;
			e.displayNone = g.displayNone === false ? false : true;
			e.actBoxClass = g.actBoxClass;
			e.disBoxClass = g.disBoxClass;
			e.disTabClass = g.disTabClass;
			e.actTabClass = g.actTabClass;
			e.onChange = g.onChange || function() {
			};
			if (g.method !== "") {
				e.method = g.method || "onmouseover";
				e.method = e.method.match(/^on.+/) ? e.method.slice(2)
						: e.method
			}
			e.oTout = null;
			e.oTab = null;
			e.delay = g.delay === undefined ? 170 : g.delay;
			e.act(e.index)
		};
		h = function() {
			var m;
			if (e.method) {
				for (m = 0; m < e.aTab.length; m++) {
					if (e.method === "mouseover" && e.delay) {
						a.addEvent(e.aTab[m], e.method, (function(n) {
							return function() {
								e.oTout = setTimeout(function() {
									e.indexTo = n;
									if (e.act(e.aTab[n]) !== false) {
										e.index = n;
										e.oTab = e
									}
								}, e.delay)
							}
						})(m));
						a.addEvent(e.aTab[m], "mouseout", function() {
							clearTimeout(e.oTout)
						})
					} else {
						a.addEvent(e.aTab[m], e.method, (function(n) {
							return function(o) {
								var p = a.fixEvent(o);
								e.indexTo = n;
								if (e.act(e.aTab[n]) !== false) {
									e.index = n;
									e.oTab = e
								}
								return a.preventDefault()
							}
						})(m))
					}
				}
			}
		};
		b = function() {
		};
		j = function() {
		};
		k = function() {
		};
		l = function() {
			i();
			c();
			d();
			h();
			b();
			j()
		};
		l();
		f.destroy = k;
		f.act = e.act;
		f.getIndex = function() {
			return e.index
		};
		return f
	}
});
STK
		.register(
				"ui.bubbleLayer",
				function(b) {
					var a = '<div class="W_layer" node-type="outer" style="display:none;"><div class="bg"><div class="effect"><table cellspacing="0" cellpadding="0" border="0"><tbody><tr><td><div class="content clearfix" node-type="inner"></div></td></tr></tbody></table><div node-type="arrow" class="#{arrow_type}"></div></div></div></div>';
					return function(c, m) {
						var g, f, i, h, o, p;
						var l = function(r) {
							r = r ? "arrow arrow_" + r : "";
							return r
						};
						var k = function(r) {
							f.style.top = r.t + "px";
							f.style.left = r.l + "px";
							return h
						};
						var j = function() {
							var r = b.core.util.winSize();
							var s = g.getSize(true);
							f.style.top = b.core.util.scrollPos()["top"]
									+ (r.height - s.h) / 2 + "px";
							f.style.left = (r.width - s.w) / 2 + "px";
							return h
						};
						var d = function(r) {
							if (typeof r === "string") {
								i.innerHTML = r
							} else {
								i.appendChild(r)
							}
							return h
						};
						var e = function(s, t) {
							var r = "";
							if ((s === "t" || s === "b")) {
								if (t === "right") {
									r = "left:auto;right:30px;"
								} else {
									if (t === "center") {
										r = "left:auto;right:"
												+ (f.offsetWidth / 2 - 8)
												+ "px;"
									}
								}
							} else {
								if ((s === "l" || s === "r") && t === "bottom") {
									r = "top:auto;bottom:20px;"
								}
							}
							o.className = l(s);
							o.style.cssText = r;
							return h
						};
						var n = function(r) {
							r = l(r);
							o.className = r;
							return h
						};
						var q = function() {
							m = l(m);
							p = a.replace(/\#\{arrow_type\}/g, m);
							g = b.module.layer(p);
							f = g.getOuter();
							i = g.getDom("inner");
							o = g.getDom("arrow");
							h = g;
							c && d(c);
							document.body.appendChild(f)
						};
						q();
						h.setPostion = k;
						h.setMiddle = j;
						h.setContent = d;
						h.setArrow = e;
						return h
					}
				});
STK.register("common.channel.relation", function(b) {
	var a = [ "follow", "unFollow", "removeFans", "block", "unBlock",
			"addFriends", "removeFriends", "updateRemark" ];
	return b.common.listener.define("common.channel.relation", a)
});
STK.register("common.trans.relation", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("mayinterested", {
		url : "/aj/user/interest/list",
		method : "get"
	});
	b("uninterested", {
		url : "/aj/user/interest/uninterested",
		method : "post"
	});
	b("userCard", {
		url : "/aj/user/card",
		method : "get"
	});
	b("follow", {
		url : "/government/reg/aj_addfollow.php ",
		method : "post"
	});
	b("unFollow", {
		url : "/aj/f/unfollow",
		method : "post"
	});
	b("block", {
		url : "/aj3/blacklist/addblacklist_v4.php",
		method : "post"
	});
	b("unBlock", {
		url : "/aj3/blacklist/delblacklist_v4.php",
		method : "post"
	});
	b("removeFans", {
		url : "/aj/f/remove",
		method : "post"
	});
	b("requestFollow", {
		url : "/ajax/relation/requestfollow",
		method : "post"
	});
	b("questions", {
		url : "/aj/invite/attlimit",
		method : "get"
	});
	b("answer", {
		url : "/aj/invite/att",
		method : "post"
	});
	b("setRemark", {
		url : "/aj3/attention/aj_remarkname_v4.php",
		method : "post"
	});
	b("recommendusers", {
		url : "/aj/f/recommendusers",
		method : "get"
	});
	b("recommendAttUsers", {
		url : "/aj/f/getrecommendusers",
		method : "get"
	});
	b("recommendPopularUsers", {
		url : "/aj/user/interest/recommendpopularusers",
		method : "get"
	});
	return a
});
STK
		.register(
				"common.relation.followPrototype",
				function(g) {
					var i = {}, a = g.common.trans.relation, k = g.common.channel.relation, j = g.kit.extra.merge;
					var e = function(m, n) {
						g.ui.alert(m.msg)
					};
					var h = function(p, n) {
						var o = g.parseParam({
							uid : "",
							f : 0,
							extra : "",
							oid : $CONFIG.oid
						}, n || {});
						if (p === "follow") {
							var m = a.getTrans(p, {
								onSuccess : function(r, t) {
									var s = j(n, r.data);
									k.fire(p, s);
									var q = n.onSuccessCb;
									typeof q === "function" && q(s)
								},
								onError : function(q, s) {
									var r = n.onErrorCb || e;
									r(q, s)
								}
							});
							m.request(o)
						} else {
							g.common.trans.relation.request(p, {
								onSuccess : function(r, t) {
									var s = j(n, r.data);
									k.fire(p, s);
									var q = n.onSuccessCb;
									typeof q === "function" && q(s)
								},
								onError : function(q, s) {
									var r = n.onErrorCb || e;
									r(q, s)
								}
							}, o)
						}
					};
					var c = function(m) {
						h("follow", m)
					};
					var l = function(m) {
						h("unFollow", m)
					};
					var f = function(m) {
						h("block", m)
					};
					var d = function(m) {
						h("unBlock", m)
					};
					var b = function(m) {
						h("removeFans", m)
					};
					i.follow = c;
					i.unFollow = l;
					i.block = f;
					i.unBlock = d;
					i.removeFans = b;
					return i
				});
STK
		.register(
				"ui.tipPrototype",
				function(a) {
					var b = 10003;
					return function(d) {
						var e, i, h, g, c;
						var f = '<div node-type="outer" style="position: absolute; clear: both; display:none;overflow:hidden;z-index:10003;" ><div node-type="inner" ></div></div>';
						e = a.parseParam({
							direct : "up",
							showCallback : a.core.func.empty,
							hideCallback : a.core.func.empty
						}, d);
						i = a.module.layer(f, e);
						h = i.getOuter();
						g = i.getInner();
						i.setTipWH = function() {
							c = this.getSize(true);
							this.tipWidth = c.w;
							this.tipHeight = c.h;
							return this
						};
						i.setTipWH();
						i.setContent = function(j) {
							if (typeof j == "string") {
								g.innerHTML = j
							} else {
								g.appendChild(j)
							}
							this.setTipWH();
							return this
						};
						i.setLayerXY = function(p) {
							if (!p) {
								throw "ui.tipPrototype need pNode as first parameter to set tip position"
							}
							var q = STK.core.dom.position(p);
							var l = q.l;
							var o = p.offsetWidth;
							var k = p.offsetHeight;
							var n = l + (o - this.tipWidth) / 2;
							var m = q.t;
							if (e.direct === "down") {
								m += k
							}
							var j = [ ";" ];
							j.push("z-index:", (b++), ";");
							j.push("width:", this.tipWidth, "px;");
							j.push("height:", this.tipHeight, "px;");
							j.push("top:", m, "px;");
							j.push("left:", n, "px;");
							h.style.cssText += j.join("")
						};
						i.aniShow = function() {
							var k = this.getOuter();
							k.style.height = "0px";
							k.style.display = "";
							var j = a.core.ani.tween(k, {
								end : e.showCallback,
								duration : 250,
								animationType : "easeoutcubic"
							});
							if (e.direct === "down") {
								j
										.play(
												{
													height : this.tipHeight
												},
												{
													staticStyle : "overflow:hidden;position:absolute;"
												})
							} else {
								var l = (parseInt(k.style.top, 10) - this.tipHeight);
								j
										.play(
												{
													height : this.tipHeight,
													top : l
												},
												{
													staticStyle : "overflow:hidden;position:absolute;"
												})
							}
						};
						i.anihide = function() {
							var k = this.getOuter();
							var m = this;
							var j = a.core.ani.tween(k, {
								end : function() {
									k.style.display = "none";
									k.style.height = m.tipHeight + "px";
									e.hideCallback()
								},
								duration : 300,
								animationType : "easeoutcubic"
							});
							if (e.direct === "down") {
								j
										.play(
												{
													height : 0
												},
												{
													staticStyle : "overflow:hidden;position:absolute;"
												})
							} else {
								var l = (parseInt(k.style.top, 10) + this.tipHeight);
								j
										.play(
												{
													height : 0,
													top : l
												},
												{
													staticStyle : "overflow:hidden;position:absolute;"
												})
							}
						};
						document.body.appendChild(h);
						i.destroy = function() {
							document.body.removeChild(h);
							i = null
						};
						return i
					}
				});
STK
		.register(
				"ui.tipConfirm",
				function(a) {
					return function(j) {
						j = j || {};
						var g = a.ui.tipPrototype(j);
						var e = g.getInner();
						var l = g.getOuter();
						l.className = "W_layer";
						e.className = "bg";
						j.info = j.info || "确认要删除这条微博吗？";
						j.icon = j.icon || "icon_ask";
						var i = j.template
								|| '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><div class="content layer_mini_info"><p class="clearfix" node-type="info"><span node-type="icon" class="'
								+ j.icon
								+ '"></span>'
								+ j.info
								+ '</p><p class="btn"><a node-type="ok" class="W_btn_b" href="javascript:void(0)"><span>确定</span></a><a class="W_btn_a" node-type="cancel" href="javascript:void(0)"><span>取消</span></a></p></div></td></tr></tbody></table>';
						var c = a.builder(i);
						g.setContent(c.box);
						if (!c.list.ok) {
							return g
						}
						var h = c.list.ok[0];
						var k = c.list.cancel[0];
						g.setIcon = function(m) {
							c.list.info.className = m;
							g.setTipWH()
						};
						g.setInfo = function(m) {
							c.list.info[0].innerHTML = '<span node-type="icon" class="'
									+ j.icon + '"></span>' + m;
							g.setTipWH()
						};
						g.cancelCallback = function() {
							if (a.getType(j.cancelCallback) == "function") {
								j.cancelCallback()
							}
						};
						g.okCallback = function() {
							if (a.getType(j.okCallback) == "function") {
								j.okCallback()
							}
						};
						var f = function() {
							g.anihide();
							g.cancelCallback()
						};
						var b = function() {
							g.anihide();
							g.okCallback()
						};
						a.addEvent(k, "click", f);
						a.addEvent(h, "click", b);
						var d = g.destroy;
						g.destroy = function() {
							a.removeEvent(k, "click", f);
							a.removeEvent(h, "click", b);
							d();
							g = null
						};
						return g
					}
				});
STK.register("common.trans.group", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("add", {
		url : "/aj/f/group/add",
		method : "post"
	});
	b("set", {
		url : "/aj3/attention/aj_group_update_v4.php",
		method : "post"
	});
	b("batchSet", {
		url : "/aj3/attention/aj_group_batchupdate_v4.php",
		method : "post"
	});
	b("list", {
		url : "/aj/f/group/list",
		method : "get"
	});
	b("order", {
		url : "/aj/f/group/order",
		method : "post"
	});
	b("verify", {
		url : "/bizapp/groupfeed/ajax/aj_verify.php",
		method : "post"
	});
	b("topage", {
		url : "/bizapp/groupfeed/ajax/aj_index.php",
		method : "get"
	});
	return a
});
STK
		.register(
				"ui.litePrompt",
				function(a) {
					var c = a.kit.extra.language;
					var b = a.core.util.easyTemplate;
					return function(d, l) {
						var j, i, g, m, f;
						var l = a.parseParam({
							hideCallback : a.core.func.empty,
							type : "succM",
							msg : "",
							timeout : ""
						}, l);
						var k = l.template
								|| '<#et temp data><div class="W_layer" node-type="outer"><div class="bg"><table cellspacing="0" cellpadding="0" border="0"><tbody><tr><td><div class="content layer_mini_info_big" node-type="inner"><p class="clearfix"><span class="icon_${data.type}"></span>${data.msg}&nbsp; &nbsp; &nbsp;</p></div></td></tr></tbody></table></div></div></#et>';
						var e = b(k, {
							type : l.type,
							msg : d
						}).toString();
						i = {};
						g = a.module.layer(e);
						f = g.getOuter();
						a.custEvent.add(g, "hide", function() {
							a.module.mask.hide();
							l.hideCallback && l.hideCallback();
							a.custEvent.remove(g, "hide", arguments.callee);
							clearTimeout(m)
						});
						a.custEvent.add(g, "show", function() {
							document.body.appendChild(f);
							a.module.mask.showUnderNode(f)
						});
						g.show();
						if (l.timeout) {
							m = setTimeout(g.hide, l.timeout)
						}
						var h = a.core.util.winSize();
						var n = g.getSize(true);
						f.style.top = a.core.util.scrollPos()["top"]
								+ (h.height - n.h) / 2 + "px";
						f.style.left = (h.width - n.w) / 2 + "px";
						i.layer = g;
						return i
					}
				});
STK
		.register(
				"common.dialog.setGroup",
				function(b) {
					var a = 16;
					return function() {
						var q = {}, z = b.kit.extra.language, F = b.ui.dialog({
							isHold : true
						}), g = b.ui.alert, H = {
							groupBox : '<div class="layer_setup_group" node-type="group_panel" ><input type="hidden" node-type="uid" name="touid"><div class="lsg_Tit form_table_single" node-type="remarkPanel">#L{备注名称：}<input node-type="remarkInput" type="text" value="#L{设置备注}" class="W_input" name="remark"></div><div class="lsg_glist"><div class="lsg_gTit clearFix"><span class="left" node-type="message"></span><span class="right W_textb" action-type="tipsLayer"><span class="icon_askS"></span>#L{为什么要设置分组？}</span><div class="layer_tips" node-type="groupTips" style="display: none;"><ul><li>#L{在首页可以设定的分组查看微博} </li><li>#L{将已经关注的人设置分组，方便管理}</li><li>#L{分组信息是保密的，只有自己可见}</li></ul><span style="left:180px" class="arrow_up"></span></div></div><div class="lsg_glistBox clearfix"><div class="W_loading" node-type="loading"><span>#L{正在加载，请稍候...}</span></div><ul class="clearfix" node-type="groupList"></ul><div node-type="addGroupPanel"><div class="lsg_addNew" node-type="showBtnBox"><a class="addnew" href="javascript:;" action-type="showBtn"><span class="ico_addinv"></span><span class="txt">#L{创建新分组}</span></a></div><div class="lsg_creaNew form_table_single" node-type="addGroupBox" style="display:none;"><input node-type="groupInput" type="text" value="#L{新分组}" class="W_input"><div style="display:none;" node-type="errorTips" class="M_notice_del"><span class="icon_del"></span></div><a href="javascript:;" class="W_btn_b btn_noloading" action-type="addGroup" node-type="addGroup"><span><b class="loading"></b><em node-type="createBtnTxt">#L{创建}</em></span></a><a href="javascript:;" action-type="hideBtn">#L{取消}</a></div></div></div></div><div class="btn"><a href="javascript:;" class="W_btn_b btn_noloading" action-type="submit" node-type="submit"><span><b class="loading"></b><em node-type="btnText">#L{保存}</em></span></a><a href="javascript:;" class="W_btn_a" action-type="cancel"><span>#L{取消}</span></a></div></div>',
							checkBox : '<input type="checkbox" value="{value}" name="gid" class="W_checkbox" {checked} id="group_{groupId}"><label for="group_{groupId}">{name}</label>'
						}, m = {
							title : "#L{关注成功}",
							gEmpty : "#L{分组名不能为空}",
							rEmpty : "#L{备注名不能为空}",
							gMaxLen : "#L{请不要超过16个字符}",
							gDefVal : "#L{新分组}",
							okLabel : "#L{设置成功}",
							rDefVal : "#L{设置备注}",
							message : "#L{为“#{nickName\\}”选择分组}",
							repeat : "#L{此分组名已存在}"
						}, B = false, r = [], A, j, f, n, e, i, C, G;
						var K = function() {
							A.remarkInput.value = z(m.rDefVal);
							A.groupInput.value = z(m.gDefVal);
							A.loading.style.display = "";
							A.groupList.innerHTML = "";
							A.showBtnBox.style.display = "";
							A.addGroupBox.style.display = "none"
						};
						var l = function(L, M) {
							var N, O;
							if (L == "addGroup") {
								N = z("#L{创建}");
								O = "createBtnTxt"
							} else {
								N = z("#L{保存}");
								O = "btnText"
							}
							if (M == "normal") {
								A[L].className = "W_btn_b btn_noloading";
								A[O].innerHTML = N
							} else {
								A[L].className = "W_btn_a_disable";
								A[O].innerHTML = z("#L{保存中...}")
							}
						};
						var J = function(M) {
							K();
							var L = b.parseParam({
								uid : "",
								fnick : "",
								hasRemark : true,
								groupList : [],
								title : z(m.title),
								successCb : function() {
								},
								cancelCb : function() {
								}
							}, M);
							C = L.successCb;
							G = L.cancelCb;
							A.uid.value = L.uid;
							if (L.hasRemark) {
								A.remarkInput.removeAttribute("disabled");
								A.remarkPanel.style.display = ""
							} else {
								A.remarkInput.setAttribute("disabled",
										"disabled");
								A.remarkPanel.style.display = "none"
							}
							L.groupList.length ? y(L.groupList) : o.request({
								uid : L.uid
							});
							A.message.innerHTML = z(m.message, {
								nickName : L.fnick
							});
							F.setTitle(L.title);
							F.appendChild(A.group_panel);
							F.show().setMiddle()
						};
						var w = function() {
							F.hide()
						};
						var p = {
							defVal : z(m.gDefVal),
							check : function(M) {
								var L = "";
								if (M === "" || M === this.defVal) {
									L = m.gEmpty
								} else {
									if (b.core.str.bLength(M) > 16) {
										L = m.gMaxLen
									}
								}
								return z(L)
							},
							checkRepeat : function(M) {
								var L = "";
								for ( var N = r.length; N--;) {
									if (M === r[N]["gname"]) {
										L = m.repeat;
										break
									}
								}
								return z(L)
							},
							showError : function(L) {
								A.errorTips.innerHTML = '<span class="icon_del"></span>'
										+ L;
								A.errorTips.style.display = ""
							},
							hideError : function() {
								A.errorTips.style.display = "none"
							}
						};
						var d = {
							defVal : z(m.rDefVal),
							check : function(M) {
								var L = "";
								if (M === "") {
									L = m.rEmpty
								} else {
									if (b.core.str.bLength(M) > 16) {
										L = m.gMaxLen
									}
								}
								return z(L)
							},
							showError : function(L) {
							},
							hideError : function() {
							}
						};
						var E = function(N) {
							var L = b.C("li");
							var M = H.checkBox.replace(/\{value\}/g, N.gid)
									.replace(/\{groupId\}/g, N.gid).replace(
											/\{name\}/g, N.gname).replace(
											/\{checked\}/g,
											N.belong ? "checked" : "");
							L.innerHTML = M;
							return L
						};
						var y = function(P) {
							r = P;
							A.addGroupPanel.style.display = (P.length >= 20) ? "none"
									: "";
							var N = document.createDocumentFragment();
							for ( var O = 0, M = P.length; O < M; O++) {
								var L = E(P[O]);
								N.appendChild(L)
							}
							A.groupList.appendChild(N);
							A.loading.style.display = "none"
						};
						var x = {
							errorCd : function(L, M) {
								b.ui.alert(L && L.msg || z("#L{保存失败！}"));
								l("submit", "normal")
							},
							getGroupSuccess : function(L, N) {
								var M = L.data || [];
								y(M)
							},
							setGroupSuccess : function(L, M) {
								w();
								l("submit", "normal");
								b.ui.litePrompt(z(m.okLabel), {
									type : "succM",
									timeout : "500"
								});
								C(L, M)
							},
							setGroupError : function(L, M) {
								p.showError(L.msg)
							},
							addGroupSuccess : function(M, Q) {
								l("addGroup", "normal");
								var O = M.data, P;
								A.addGroupPanel.style.display = (O.length >= 20) ? "none"
										: "";
								for ( var N = O.length; N--;) {
									if (O[N].belong === 1) {
										P = O[N];
										break
									}
								}
								P && r.push(P);
								var L = E(P);
								A.groupList.appendChild(L);
								c.hideAddPanel();
								A.groupInput.value = z(m.gDefVal)
							}
						};
						var o = b.common.trans.group.getTrans("list", {
							onSuccess : x.getGroupSuccess,
							onError : x.errorCd
						});
						var v = b.common.trans.group.getTrans("set", {
							onSuccess : x.setGroupSuccess,
							onError : x.errorCd,
							onFail : x.errorCd
						});
						var u = b.common.trans.group.getTrans("batchSet", {
							onSuccess : x.setGroupSuccess,
							onError : x.errorCd
						});
						var h = b.common.trans.group.getTrans("add", {
							onSuccess : x.addGroupSuccess,
							onError : function(L, M) {
								l("addGroup", "normal");
								b.ui.alert(L.msg)
							}
						});
						var c = {
							showAddPanel : function() {
								A.showBtnBox.style.display = "none";
								A.addGroupBox.style.display = "";
								A.groupInput.focus()
							},
							hideAddPanel : function() {
								A.showBtnBox.style.display = "";
								A.addGroupBox.style.display = "none";
								p.hideError();
								A.groupInput.value = p.defVal
							},
							addGroup : function() {
								var L = b.trim(A.groupInput.value), M = p
										.check(L)
										|| p.checkRepeat(L);
								if (M) {
									p.showError(M)
								} else {
									p.hideError();
									l("addGroup", "loading");
									h.request({
										name : L
									})
								}
							},
							submit : function() {
								var N = b.trim(A.groupInput.value), O = p
										.checkRepeat(N);
								if (O) {
									p.showError(O);
									return
								}
								B = true;
								p.hideError();
								var M = b.htmlToJson(A.group_panel);
								var L = [];
								M.gid = L.concat(M.gid).join(",");
								p.check(N) || (M.newgroup = N);
								if (M.remark === z(m.rDefVal)) {
									M.remark = ""
								}
								l("submit", "loading");
								v.request(M)
							},
							cancel : function() {
								B = false;
								w()
							},
							inputFocus : function(L) {
								return function(M) {
									var M = b.fixEvent(M), N = M.target, O = N.value;
									L.hideError();
									(O === L.defVal) && (N.value = "")
								}
							},
							inputBlur : function(L) {
								return function(M) {
									var M = b.fixEvent(M), N = M.target, O = b
											.trim(N.value);
									O || (N.value = L.defVal)
								}
							},
							inputMaxLen : function(M) {
								var M = b.fixEvent(M), N = M.target, O = N.value, L = b.core.str
										.bLength(O);
								(L > 16) && (N.value = b.core.str.leftB(O, a))
							},
							showGroupTips : function() {
								A.groupTips.style.display = ""
							},
							hideGroupTips : function() {
								A.groupTips.style.display = "none"
							}
						};
						var D = function() {
							k();
							s();
							t()
						};
						var s = function() {
							j = b.core.evt.delegatedEvent(A.group_panel);
							f = c.inputFocus(p);
							n = c.inputBlur(p);
							e = c.inputFocus(d);
							i = c.inputBlur(d);
							b.addEvent(A.remarkInput, "focus", e);
							b.addEvent(A.remarkInput, "blur", i);
							b.addEvent(A.groupInput, "focus", f);
							b.addEvent(A.groupInput, "blur", n);
							b.addEvent(A.remarkInput, "keyup", c.inputMaxLen);
							b.addEvent(A.groupInput, "keyup", c.inputMaxLen);
							j.add("showBtn", "click", c.showAddPanel);
							j.add("hideBtn", "click", c.hideAddPanel);
							j.add("addGroup", "click", c.addGroup);
							j.add("submit", "click", c.submit);
							j.add("cancel", "click", c.cancel);
							j.add("tipsLayer", "mouseover", c.showGroupTips);
							j.add("tipsLayer", "mouseout", c.hideGroupTips)
						};
						var t = function() {
							b.custEvent.add(F, "hide", function() {
								B || G()
							})
						};
						var k = function() {
							var L = b.core.dom.builder(z(H.groupBox));
							A = b.kit.dom.parseDOM(L.list)
						};
						var I = function() {
							b.removeEvent(A.remarkInput, "focus", e);
							b.removeEvent(A.remarkInput, "blur", i);
							b.removeEvent(A.groupInput, "focus", f);
							b.removeEvent(A.groupInput, "blur", n);
							b
									.removeEvent(A.remarkInput, "keyup",
											c.inputMaxLen);
							b.removeEvent(A.groupInput, "keyup", c.inputMaxLen);
							f = null;
							n = null;
							e = null;
							i = null;
							j && j.destroy()
						};
						D();
						q.show = J;
						q.hide = w;
						q.destroy = I;
						return q
					}
				});
STK
		.register(
				"ui.confirm",
				function(c) {
					var b = '<div node-type="outer" class="layer_point"><dl class="point clearfix"><dt><span class="" node-type="icon"></span></dt><dd node-type="inner"><p class="W_texta" node-type="textLarge"></p><p class="W_textb" node-type="textComplex"></p><p class="W_textb" node-type="textSmall"></p></dd></dl><div class="btn"><a href="javascript:void(0)" class="W_btn_b" node-type="OK"></a><a href="javascript:void(0)" class="W_btn_a" node-type="cancel"></a></div></div>';
					var e = {
						success : "icon_succM",
						error : "icon_errorM",
						warn : "icon_warnM",
						"delete" : "icon_delM",
						question : "icon_questionM"
					};
					var d = c.kit.extra.language;
					var a = null;
					return function(g, m) {
						var l, k, h, n, j, f;
						l = c.parseParam({
							title : d("#L{提示}"),
							icon : "question",
							textLarge : g,
							textComplex : "",
							textSmall : "",
							OK : c.funcEmpty,
							OKText : d("#L{确定}"),
							cancel : c.funcEmpty,
							cancelText : d("#L{取消}")
						}, m);
						l.icon = e[l.icon];
						k = {};
						if (!a) {
							a = c.kit.extra.reuse(function() {
								var o = c.module.layer(b);
								return o
							})
						}
						h = a.getOne();
						n = c.ui.dialog();
						n.setContent(h.getOuter());
						h.getDom("icon").className = l.icon;
						h.getDom("textLarge").innerHTML = l.textLarge;
						h.getDom("textComplex").innerHTML = l.textComplex;
						h.getDom("textSmall").innerHTML = l.textSmall;
						h.getDom("OK").innerHTML = "<span>" + l.OKText
								+ "</span>";
						h.getDom("cancel").innerHTML = "<span>" + l.cancelText
								+ "</span>";
						n.setTitle(l.title);
						var i = function() {
							j = true;
							f = c.htmlToJson(h.getDom("textComplex"));
							n.hide()
						};
						c.addEvent(h.getDom("OK"), "click", i);
						c.addEvent(h.getDom("cancel"), "click", n.hide);
						c.custEvent.add(n, "hide", function() {
							c.custEvent.remove(n, "hide", arguments.callee);
							c.removeEvent(h.getDom("OK"), "click", i);
							c.removeEvent(h.getDom("cancel"), "click", n.hide);
							a.setUnused(h);
							if (j) {
								l.OK(f)
							} else {
								l.cancel(f)
							}
						});
						n.show().setMiddle();
						k.cfm = h;
						k.dia = n;
						return k
					}
				});
STK
		.register(
				"common.relation.baseFollow",
				function(c) {
					var b = window.$CONFIG || {};
					var a = b.imgPath + "/style/images/common/transparent.gif";
					return function(q) {
						var i = {}, m = c.common.channel.relation, f = c.core.evt
								.delegatedEvent(q), t = c.common.relation.followPrototype, h = c.kit.extra.merge, o = c.kit.extra.language, s = c.common.dialog
								.setGroup(), g = {
							unFollowTips : o("#L{确认取消关注吗？}"),
							bothFollow : o("#L{互相关注}"),
							singleFollow : o("#L{已关注}")
						}, x = c.ui.tipConfirm({
							info : g.unFollowTips
						}), w = {
							follow : o('<div class="W_addbtn_even"><img src="'
									+ a
									+ '" title="#L{加关注}"class="icon_add {className}" alt=""/>{focDoc}<span class="W_vline">|</span><a class="W_linkb" action-type="{actionType}" action-data="uid={uid}&fnick={fnick}&f=1" href="javascript:void(0);"><em>#L{取消}</em></a></div>'),
							unFollow : o('<a action-type="follow" action-data="uid={uid}&fnick={fnick}&f=1" href="javascript:void(0);" class="W_btn_b"><span>{followMe}<img class="icon_add addbtn_b" title="#L{加关注}" src="'
									+ a + '">#L{加关注}</span></a>'),
							block : o('<div class="W_addbtn_even">#L{已加入黑名单}<span class="W_vline">|</span><a action-type="unBlock" action-data="uid={uid}&fnick={fnick}&f=1" href="javascript:void(0);" class="W_linkb"><em>#L{解除}</em></a></div>'),
							loading : o('<b class="loading"></b>#L{加关注}'),
							followMe : o('<img class="icon_add addbtn_g" title="#L{加关注}" src="'
									+ a + '"><em class="vline"></em>')
						};
						var e = function(B, y) {
							y = y || {};
							var A = B;
							for ( var z in y) {
								A = A.replace("{" + z + "}", y[z])
							}
							return A
						};
						var n = function(z) {
							var y = c.core.dom.sizzle("a[action-data*=uid=" + z
									+ "]", q)[0], B;
							if (!y) {
								return
							}
							do {
								var A = y.getAttribute("node-type");
								if (A === "followBtnBox") {
									B = y;
									break
								}
								y = y.parentNode
							} while (y && y.tagName.toLowerCase() !== "body");
							return B
						};
						var u = {
							followed : function(z) {
								var B = n(z.uid);
								if (!B) {
									return
								}
								var D = "addbtn_d", E = g.singleFollow, A = "unFollow", C = z.relation
										|| {};
								if (C.following && C.follow_me) {
									D = "addbtn_c";
									E = g.bothFollow;
									A = "unFollow"
								}
								var y = e(w.follow, {
									className : D,
									focDoc : E,
									actionType : A,
									uid : z.uid,
									fnick : z.fnick
								});
								B.innerHTML = y || ""
							},
							unFollow : function(y) {
								var z = n(y.uid);
								if (!z) {
									return
								}
								var A = y.relation || {};
								temp = e(w.unFollow, {
									followMe : A.follow_me ? w.followMe : "",
									uid : y.uid,
									fnick : y.fnick
								});
								z.innerHTML = temp
							},
							blocked : function(z) {
								var A = n(z.uid);
								if (!A) {
									return
								}
								var y = e(w.block, {
									uid : z.uid,
									fnick : z.fnick
								});
								A.innerHTML = y
							}
						};
						var l = {
							followListener : function(y) {
								u.followed(y)
							},
							unFollowListener : function(y) {
								u.unFollow(y)
							},
							blockListener : function(y) {
								u.blocked(y)
							},
							unBlockListener : function(y) {
								u.unFollow(y)
							},
							removeFansListener : function(y) {
								var z = y.relation || {};
								if (z.following) {
									u.followed(y)
								} else {
									u.unFollow(y)
								}
							}
						};
						var d = {
							follow : function(y) {
								var A = c.sizzle("span", y.el)[0];
								A.innerHTML = w.loading;
								var z = c.kit.extra.merge({
									onSuccessCb : function(B) {
										s.show({
											uid : B.uid,
											fnick : B.fnick,
											groupList : B.group,
											hasRemark : true
										})
									}
								}, y.data || {});
								t.follow(z)
							},
							unFollow : function(y) {
								x.setLayerXY(y.el);
								x.aniShow();
								x.okCallback = function() {
									t.unFollow(y.data)
								}
							},
							unBlock : function(y) {
								c.ui.confirm(o("#L{确认将此用户从你的黑名单中解除吗？}"), {
									OK : function() {
										t.unBlock(y.data)
									}
								})
							}
						};
						var r = function() {
							j();
							k()
						};
						var j = function() {
							m.register("block", l.blockListener);
							m.register("follow", l.followListener);
							m.register("unBlock", l.unBlockListener);
							m.register("unFollow", l.unFollowListener);
							m.register("removeFans", l.removeFansListener)
						};
						var k = function() {
							f.add("follow", "click", d.follow);
							f.add("unBlock", "click", d.unBlock);
							f.add("unFollow", "click", d.unFollow)
						};
						var p = function(y) {
							if (!c.core.dom.isNode(y)) {
								throw "[STK.common.relation.baseFollow]:node is not a Node!"
							}
						};
						var v = function() {
							f.destroy();
							s.destroy();
							m.remove("block", l.blockListener);
							m.remove("follow", l.followListener);
							m.remove("unBlock", l.unBlockListener);
							m.remove("unFollow", l.unFollowListener);
							m.remove("removeFans", l.removeFansListener);
							l = null
						};
						r();
						i.destroy = v;
						return i
					}
				});
STK
		.register(
				"common.layer.userCard",
				function(h) {
					var g = 5, b = 28, i = 38, a = 50;
					var d = h.kit.extra.language;
					var j = function(o, p, x) {
						var q, m, w, v;
						var n = h.core.util.scrollPos(), u = h.core.dom
								.position(o), s = h.core.util.winSize();
						w = u.t - n.top;
						q = u.l - n.left;
						m = s.width - q - p;
						v = s.height - w - x;
						return {
							t : w,
							l : q,
							r : m,
							b : v,
							x : u.l,
							y : u.t
						}
					};
					var c = function(v) {
						var l = v.nodeW, w = v.nodeH, m = v.dis, s = v.cardWidth, o = v.cardHeight, t = v.arrow, n = v.node, p = v.offsetH, u = v.offsetW, q = v.arPos, r = {};
						switch (t) {
						case "t":
							r.l = m.x - u + l / 2;
							r.t = m.y - g - o;
							break;
						case "r":
							r.l = m.x + l + g;
							r.t = m.y - p + w / 2;
							break;
						case "b":
							r.l = m.x - u + l / 2;
							r.t = m.y + w + g;
							break;
						case "l":
						default:
							r.l = m.x - s - g;
							r.t = m.y - p + w / 2;
							break
						}
						return r
					};
					var f = function(s) {
						var x = s.node, B = s.cardWidth, m = s.cardHeight, t = s.arrowPos
								|| "auto", y = (s.order || "l,b,t,r")
								.split(","), w = y[0], p = Math.max(m, 300), l = {
							l : B,
							b : p,
							t : p,
							r : B
						}, E = {
							l : "r",
							b : "t",
							t : "b",
							r : "l"
						}, q = x.offsetWidth, u = x.offsetHeight, v = j(x, q, u), n = B
								- i - q / 2, o = b, D = i;
						for ( var z = 0, A = y.length; z < A; z++) {
							var C = y[z];
							if (v[C] > l[C]) {
								w = C;
								break
							}
						}
						if (t === "auto") {
							if (w === "t" || w === "b") {
								if (q / 2 + v.r < B - i) {
									t = "right"
								}
							} else {
								if (u / 2 + v.b < m - b) {
									t = "bottom"
								}
							}
						}
						if (t === "right") {
							D = B - i
						} else {
							if (t === "bottom") {
								o = m - b
							} else {
								if (t === "center") {
									D = B / 2
								}
							}
						}
						var r = c({
							nodeW : q,
							nodeH : u,
							dis : v,
							cardWidth : B,
							cardHeight : m,
							arrow : w,
							node : x,
							offsetH : o,
							offsetW : D
						});
						return {
							dire : E[w],
							pos : r,
							arPos : t
						}
					};
					var e = function(l, m) {
						return function() {
							return l.apply(m, arguments)
						}
					};
					var k = function() {
						this.bubLayer = h.ui.bubbleLayer();
						this.cardPanel = this.bubLayer.getOuter();
						this.initBind();
						this.initPlugin()
					};
					k.prototype = {
						initBind : function() {
							var l = e(this.stopHide, this);
							var m = e(this.hideCard, this);
							h.addEvent(this.cardPanel, "mouseover", l);
							h.addEvent(this.cardPanel, "mouseout", m);
							this.dEvent = h.core.evt
									.delegatedEvent(this.cardPanel)
						},
						initPlugin : function() {
							h.common.relation.baseFollow(this.cardPanel)
						},
						stopShow : function() {
							this.showTimer && clearTimeout(this.showTimer)
						},
						stopHide : function() {
							this.hideTimer && clearTimeout(this.hideTimer)
						},
						showCard : function(l) {
							var m = l.zIndex || 9999;
							this.cardPanel.style.zIndex = m;
							this.bubLayer.setContent(l.content).show();
							this.node = l.node;
							this.arrowPos = l.arrowPos;
							this.order = l.order;
							this.direPos = f({
								node : this.node,
								cardWidth : this.cardPanel.offsetWidth,
								cardHeight : this.cardPanel.offsetHeight,
								arrowPos : this.arrowPos,
								order : this.order
							});
							this.bubLayer.setPostion(this.direPos.pos)
									.setArrow(this.direPos.dire,
											this.direPos.arPos)
						},
						setContent : function(m) {
							var n = this.cardPanel.offsetHeight;
							this.bubLayer.setContent(m);
							if (this.direPos.dire === "b") {
								var l = this.cardPanel.offsetHeight - n;
								this.bubLayer.setPostion({
									l : this.direPos.pos.l,
									t : this.direPos.pos.t - l
								})
							}
						},
						hideCard : function() {
							var l = this;
							this.hideTimer = setTimeout(function() {
								l.bubLayer.hide()
							}, a)
						}
					};
					return function() {
						return new k()
					}
				});
STK.register("common.trans.userCard", function(c) {
	var a = c.kit.io.inter();
	var b = a.register;
	b("getCard", {
		url : "/aj/usercard",
		method : "post"
	});
	return a
});
STK.register("kit.dom.parentElementBy", function(a) {
	return function(f, b, d) {
		if (!f || !b) {
			throw new Error("传入的参数为空")
		}
		var c = 0, e;
		f = f.parentNode;
		while (f.parentNode) {
			c++;
			e = d(f);
			if (e === false) {
				return false
			} else {
				if (e === true) {
					return f
				} else {
					if (e === b) {
						return null
					}
				}
			}
			f = f.parentNode;
			if (c > 30000) {
				return false
			}
		}
		return null
	}
});
STK
		.register(
				"common.userCard",
				function(d) {
					var a = 400;
					var b = "usercard";
					var c = d.kit.extra.language;
					var e = {
						objs : {
							trans : {
								userCard : {
									getCard : d.common.trans.userCard
											.getTrans(
													"getCard",
													{
														onSuccess : function(g,
																f) {
															e.objs.userCard
																	.setContent(g.data);
															e.objs.cache.push(
																	f.uid,
																	g.data)
														},
														onError : function(f) {
														},
														onFail : function(f) {
														}
													})
								}
							},
							cache : {
								data : {},
								pull : function(i) {
									var g = this.data[i], h = null;
									if (g) {
										var f = new Date();
										if (f - g.date < 300000) {
											h = g.html
										} else {
											delete this.data[i]
										}
									}
									return h
								},
								push : function(g, f) {
									this.data[g] = {
										date : new Date(),
										html : f
									}
								},
								remove : function(f) {
									delete this.data[f]
								},
								clear : function() {
									this.data = {}
								}
							}
						},
						DEventFun : {
							clickFollow : function(g) {
								var f = d.kit.dom
										.parentElementBy(
												g.el,
												document.documentElement,
												function(h) {
													if (h
															.getAttribute("node-type") === "followBtnBox") {
														return true
													}
												});
								d
										.addClassName(d.sizzle("img", f)[0],
												"loading");
								d.common.relation.followPrototype.follow({
									uid : g.data.uid,
									f : "1",
									btn : f,
									key : "commonUserCard",
									extra : window
											.encodeURIComponent(g.data.extra)
								})
							},
							clickUnfollow : function(g) {
								var f = d.kit.dom
										.parentElementBy(
												g.el,
												document.documentElement,
												function(h) {
													if (h
															.getAttribute("node-type") === "followBtnBox") {
														return true
													}
												});
								d.common.relation.followPrototype.unFollow({
									uid : g.data.uid,
									f : "0",
									btn : f,
									key : "commonUserCard"
								})
							}
						},
						channelFun : {
							relationFollow : function(g) {
								e.objs.cache.remove(g.uid);
								if (g.key !== "commonUserCard") {
									return
								}
								var f;
								setTimeout(
										function() {
											if (g.relation.following) {
												if (g.relation.follow_me) {
													f = c('<div class="W_addbtn_even"><img class="icon_add addbtn_c" src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif?version='
															+ $CONFIG.version
															+ '">#L{互相关注}<span class="W_vline" />|</span><a href="javascript:;" class="W_linkb" action-data="uid='
															+ g.uid
															+ '" action-type="unFollow"><em>#L{取消}</em></a></div>')
												} else {
													f = c('<div class="W_addbtn_even"><img class="icon_add addbtn_d" title="#L{加关注}" src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif?version='
															+ $CONFIG.version
															+ '" />#L{已关注}<span class="W_vline">|</span><a href="javascript:;" class="W_linkb"  action-data="uid='
															+ g.uid
															+ '"action-type="unFollow"><em>#L{取消}</em></a></div>')
												}
												d.ui.alert(c("#L{关注成功。}"), {
													icon : "success",
													OK : function() {
													},
													timeout : 2000
												})
											} else {
												f = c('<a action-data="uid='
														+ g.uid
														+ '" href="javascript:;" action-type="follow" class="W_btn_b"><span><img class="icon_add addbtn_b" title="#L{加关注}" src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif?version='
														+ $CONFIG.version
														+ '">#L{加关注}</span></a>')
											}
											d.removeClassName(d.sizzle("img",
													g.btn)[0], "loading");
											if (g.btn) {
												g.btn.innerHTML = f
											}
										}, 1000)
							},
							relationClearIdList : function(g) {
								var f = g.uid.split(",");
								d.foreach(f, function(h) {
									e.objs.cache.remove(h)
								})
							}
						}
					};
					return function(h, p) {
						var m, g, i, l, f, n, o, q, k = {};
						var j = {
							conf : d
									.parseParam(
											{
												order : "r,b,l,t",
												zIndex : 9999,
												type : 0,
												variety : "userCard",
												arrowPos : "auto",
												loadTemp : c('<div class="W_loading" style="width:360px;padding-top:15px;padding-bottom:15px;text-align:center"><span>#L{正在加载，请稍候}...</span></div>')
											}, p || {}),
							DOM : {},
							objs : {},
							DOM_eventFun : {
								mouseoverNode : function(t) {
									t = d.fixEvent(t);
									var r = t.target;
									var s = r.getAttribute(b);
									if (s) {
										e.objs.userCard.stopHide();
										e.objs.userCard.showTimer = setTimeout(
												function() {
													j.showCard(r, s)
												}, a)
									}
								},
								mouseoutNode : function(s) {
									s = d.fixEvent(s);
									var r = s.target;
									var t = r.getAttribute(b);
									clearTimeout(e.objs.userCard.showTimer);
									if (t) {
										e.objs.userCard.hideTimer = setTimeout(
												function() {
													e.objs.userCard.stopShow();
													e.objs.userCard.hideCard();
													e.objs.trans.userCard.getCard
															.abort()
												}, a)
									}
								}
							},
							showCard : function(r, s) {
								var u = d.queryToJson(s);
								var t = u.uid;
								e.objs.userCard.showCard({
									content : j.cHtml || j.conf.loadTemp,
									node : r,
									order : j.conf.order,
									arrowPos : j.conf.arrowPos,
									zIndex : j.conf.zIndex
								});
								var v = e.objs.cache.pull(t);
								if (v) {
									e.objs.userCard.setContent(v)
								} else {
									e.objs.trans.userCard.getCard.request(u)
								}
							}
						};
						m = function() {
							if (!h) {
								throw new Error("common.userCard node 没有定义")
							}
						};
						g = function() {
							j.DOM = d.kit.dom.parseDOM(d.builder(h).list);
							if (!1) {
								throw new Error("common.userCard 必需的节点 不存在")
							}
						};
						i = function() {
							if (!e.objs.userCard) {
								e.objs.userCard = d.common.layer.userCard();
								e.objs.DEvent = d.core.evt
										.delegatedEvent(e.objs.userCard.bubLayer
												.getOuter());
								e.objs.DEvent.add("follow", "click",
										e.DEventFun.clickFollow);
								e.objs.DEvent.add("unFollow", "click",
										e.DEventFun.clickUnfollow);
								d.common.channel.relation.register("follow",
										e.channelFun.relationFollow);
								d.common.channel.relation.register("unFollow",
										e.channelFun.relationFollow);
								d.common.channel.relation.register(
										"clearIdList",
										e.channelFun.relationClearIdList)
							}
						};
						l = function() {
							if ($CONFIG.islogin) {
								d.addEvent(h, "mouseover",
										j.DOM_eventFun.mouseoverNode);
								d.addEvent(h, "mouseout",
										j.DOM_eventFun.mouseoutNode)
							}
						};
						f = function() {
						};
						n = function() {
						};
						o = function() {
							var r;
							if (j) {
								if (e.objs.userCard) {
									clearTimeout(e.objs.userCard.showTimer);
									e.objs.userCard.bubLayer.hide()
								}
								d.removeEvent(h, "mouseover",
										j.DOM_eventFun.mouseoverNode);
								d.removeEvent(h, "mouseout",
										j.DOM_eventFun.mouseoutNode);
								d.foreach(j.objs, function(s) {
									if (s.destroy) {
										s.destroy()
									}
								});
								j = null
							}
						};
						q = function() {
							m();
							g();
							i();
							l();
							f();
							n()
						};
						q();
						k.destroy = o;
						return k
					}
				});
STK.register("comp.home.leftNav", function(d) {
					var b = "nav_Open";
					var c = "open_x";
					var a = "cur";
					$L = d.kit.extra.language;
					
					return function(g) {
						var l, f, h, k, e, m, n, o, j = {};
						var i = {
							DOM : {},
							objs : {},
							DOM_eventFun : {
								clickSendMessageBtn : function(p) {
									var r = i.DOMs.sendMessageBtn[0];
									var q = d.queryToJson(r
											.getAttribute("action-data"));
									if (!r.dialogSendLiteMessage) {
										r.dialogSendLiteMessage = d.common.dialog
												.sendLiteMessage(q)
									}
									r.dialogSendLiteMessage.show()
								},
								clickSendFollowSinatBtn : function(q) {
									var s = i.DOMs.followSinatBtn[0];
									var r = d.queryToJson(s
											.getAttribute("action-data")).uid;
									var p = d.queryToJson(s
											.getAttribute("action-data")).extra;
									d.common.relation.followPrototype
											.follow({
												uid : r,
												f : "1",
												btn : s,
												key : "comp.home.leftNav_clickSendFollowSinatBtn",
												extra : window
														.encodeURIComponent(p)
											})
								},
								clickSearchBtn : function(p) {
									var r = i.DOMs.searchForm[0];
									var q = " http://s.weibo.com/user/";
									q = q + "&nickname="
											+ i.DOMs.searchInput[0].value
											+ "&auth=vip";
									i.DOMs.searchForm[0].action = q;
									d.fireEvent(r, "submit");
									r.submit()
								}
							},
							channelFun : {
								relationFollow : function(r) {
									if (r.key !== "comp.home.leftNav_clickSendFollowSinatBtn") {
										return
									}
									var p = d.C("a"), q = d.C("span");
									d.addClassName(p, "W_btn_b_disable");
									p.setAttribute("disable", true);
									q.innerHTML = $L("#L{已关注}");
									p.appendChild(q);
									r.btn.parentNode.replaceChild(p, r.btn)
								}
							},
							DEventFun : {
								mouseoverMenuShow : function(p) {
									i.showSubMenu(p.el);
									i.DOMs.navBox[0].className += ""
								},
								mouseoutMenuShow : function(p) {
									i.hideSubMenu(p.el);
									i.DOMs.navBox[0].className += ""
								}
							},
							clearOverClassName : function() {
								d.foreach(i.DOMs.menuShow, function(p) {
									d.removeClassName(p, b)
								})
							},
							getSubMenu : function(p) {
								if (!p.subMenu) {
									p.subMenu = d.sizzle("[node-type=subMenu]",
											p)[0]
								}
								return p.subMenu
							},
							showSubMenu : function(s) {
								var p = i.getSubMenu(s);
								var q = d.position(s).t - d.scrollPos().top;
								var r;
								if (p) {
									d.addClassName(s, b);
									bShowSubMenuUpward = (d.winSize().height - q) < p.offsetHeight;
									if (bShowSubMenuUpward) {
										d.addClassName(s, c)
									} else {
										d.removeClassName(s, c)
									}
								}
							},
							hideSubMenu : function(p) {
								i.clearOverClassName()
							}
						};
						l = function() {
							if (!g) {
								throw new Error("node没有定义")
							}
						};

						f = function() {
							i.DOMs = d.builder(g).list;
							i.DOMs.menuShow = d.sizzle(
									"*[action-type=menuShow]", g);
							if (!1) {
								throw new Error("必需的节点 不存在")
							}
						};
						h = function() {
							i.objs.oTabs = d.module.tab({
								aTab : i.DOMs.navTab,
								actTabClass : a,
								aBox : i.DOMs.navBox,
								method : "click"
							});
							i.objs.userCard = d.common.userCard(g)
						};

						k = function() {
							i.objs.DEvent = d.core.evt.delegatedEvent(g);
							i.objs.DEvent.add("menuShow", "mouseover",
									i.DEventFun.mouseoverMenuShow);
							i.objs.DEvent.add("menuShow", "mouseout",
									i.DEventFun.mouseoutMenuShow);
							if (i.DOMs.sendMessageBtn) {
								d.addEvent(i.DOMs.sendMessageBtn[0], "click",
										i.DOM_eventFun.clickSendMessageBtn)
							}
							if (i.DOMs.followSinatBtn) {
								d.addEvent(i.DOMs.followSinatBtn[0], "click",
										i.DOM_eventFun.clickSendFollowSinatBtn)
							}
							d.addEvent(i.DOMs.searchBtn[0], "click",
									i.DOM_eventFun.clickSearchBtn)
						};
						e = function() {
						};
						m = function() {
							d.common.channel.relation.register("follow",
									i.channelFun.relationFollow)
						};
						n = function() {
							if (i) {
								if (i.DOMs.sendMessageBtn) {
									d.removeEvent(i.DOMs.sendMessageBtn[0],
											"click",
											i.DOM_eventFun.clickSendMessageBtn)
								}
								if (i.DOMs.followSinatBtn) {
									d
											.removeEvent(
													i.DOMs.followSinatBtn[0],
													"click",
													i.DOM_eventFun.clickSendFollowSinatBtn)
								}
								d.removeEvent(i.DOMs.searchBtn[0], "click",
										i.DOM_eventFun.clickSearchBtn);
								d.common.channel.relation.remove("follow",
										i.channelFun.relationFollow);
								d.foreach(i.objs, function(p) {
									if (p.destroy) {
										p.destroy()
									}
								});
								i = null
							}
						};
						o = function() {
							l();
							f();
							h();
							k();
							e();
							m()
						};
						o();
						j.destroy = n;
						return j
					}
				});
STK.pageletM.register("pl.home.leftNav", function(c) {
	var b = c.E("pl_home_leftNav");
	var a = c.comp.home.leftNav(b);
	return a
});
STK.register("comp.home.ranking", function(b) {
	var a = "current";
	return function(f) {
		var k, d, g, j, c, l, m, n, i = {};
		var e = b.delegatedEvent(f);
		var h = {
			DOM : {},
			objs : {},
			DOM_eventFun : {
				follow : function(o) {
					h.objs.followTrans.getTrans("follow", {
						onSuccess : function(p, q) {
							o.el.innerHTML = "已关注";
							o.el.className = "top_concer_yet"
						},
						onError : function(p, q) {
						}
					}).request(o.data)
				}
			}
		};
		k = function() {
			if (!f) {
				throw new Error("comp.home.ranking node 没有定义")
			}
		};
		d = function() {
			h.DOMs = b.builder(f).list;
			if (!1) {
				throw new Error("comp.home.ranking 必需的节点 不存在")
			}
		};
		g = function() {
			h.objs.otabs = b.module.tab({
				aTab : h.DOMs.rankTab,
				actTabClass : a,
				aBox : h.DOMs.rankBox,
				method : "click"
			});
			h.objs.userCard = b.common.userCard(f);
			h.objs.followTrans = b.common.trans.relation
		};
		j = function() {
			e.add("follow", "click", h.DOM_eventFun.follow)
		};
		c = function() {
		};
		l = function() {
		};
		m = function() {
			if (h) {
				b.foreach(h.objs, function(p) {
					if (p.destroy) {
						p.destroy()
					}
				});
				h = null
			}
		};
		n = function() {
			k();
			d();
			g();
			j();
			c();
			l()
		};
		n();
		i.destroy = m;
		return i
	}
});
STK.pageletM.register("pl.home.ranking", function(c) {
	var b = c.E("pl_home_ranking");
	var a = c.comp.home.ranking(b);
	return a
});
STK
		.register(
				"comp.base.scrollToTop",
				function(a) {
					return function(h, b) {
						if (h == null) {
							a
									.log("[comp.base.scrollToTop]: scrollToTop need a node[id=pl_base_scrollToTop] in BODY.");
							return {
								destroy : a.funcEmpty
							}
						}
						var m = {};
						m.DOM = a.kit.dom.parseDOM(a.builder(h).list);
						var i;
						var j;
						var f, o, e;
						var g = (a.getStyle(h, "position") != "fixed");
						function k() {
							var s = a.scrollPos();
							var q = s.top;
							var p, r;
							if (q > 0) {
								a.setStyle(h, "visibility", "visible");
								if (g) {
									p = a.winSize().height;
									r = q + p - 190;
									a.setStyle(h, "top", r)
								}
							} else {
								a.setStyle(h, "visibility", "hidden")
							}
						}
						function d() {
							if (i != null) {
								if (new Date().getTime() - i < 500) {
									clearTimeout(j);
									j = null
								}
							}
							i = new Date().getTime();
							j = setTimeout(k, 100)
						}
						function c() {
							document.body.scrollIntoView();
							return false
						}
						e = a.sizzle(".star_main", document.body);
						function n() {
							if (e == "") {
								return
							}
							var r = a.core.dom.getSize(e[0]).height;
							if (o != null && o != r) {
								var q = parseInt(a.getStyle(h, "top"));
								var p = a.winSize().height;
								if (q > r) {
									a.setStyle(h, "top", r - 25)
								}
								if (p >= r) {
									a.setStyle(h, "visibility", "hidden")
								} else {
									k()
								}
							}
							o = r
						}
						f = setInterval(n, 200);
						a.addEvent(window, "scroll", d);
						a.addEvent(m.DOM.goTop, "click", c);
						d();
						var l = {
							destroy : function() {
								a.removeEvent(window, "scroll", d);
								a.removeEvent(m.DOM.goTop, "onclick", c);
								if (f != null) {
									clearInterval(f);
									f = null
								}
							}
						};
						return l
					}
				});
STK.pageletM.register("pl.base.scrollToTop", function(c) {
	var b = c.E("pl_base_scrollToTop");
	var a = c.comp.base.scrollToTop(b);
	return a
});
STK
		.register(
				"comp.home.map",
				function(e) {
					var g = '<li><a href="$url" target="_blank">$name</a></li>';
					var c = ' <a href="$url" target="_blank">$name</a> ';
					var i = 770;
					var b = 261;
					var j = 200, f = 150;
					var d = "/Sns/school_maps/get_school_list";
					var a = [ "北京", "上海", "天津", "重庆" ];
					var h = e.core.util.browser;
					return function(z) {
						var y, l, C, r, t, v, F, B, s = {};
						var p = e.delegatedEvent(z);
						var x = {}, n, k, q, D, G;
						var E, m;
						var w, u;
						var o;
						var A = {
							DOM : {},
							objs : {},
							DOM_eventFun : {
								popMouseoverFn : function() {
									clearTimeout(m)
								},
								popMouseoutFn : function() {
									m = setTimeout(function() {
										o = null;
										A.hide()
									}, f)
								},
								areaActionMouseoverFn : function(H) {
									e.stopEvent();
									D = x[H.data.pid];
									if (D == o || !D || !D.map) {
										return
									}
									clearTimeout(E);
									clearTimeout(m);
									E = setTimeout(function() {
										o = D;
										A.show(D)
									}, j);
									if (!h.IE9) {
										e.setStyle(D.map, "opacity", 1)
									} else {
										D.map.style.opacity = 1
									}
									return false
								},
								areaActionMouseoutFn : function(H) {
									e.stopEvent();
									clearTimeout(E);
									clearTimeout(m);
									D = x[H.data.pid];
									if (!D || !D.map) {
										return
									}
									m = setTimeout(function() {
										o = null;
										A.hide()
									}, f);
									if (!h.IE9) {
										e.setStyle(D.map, "opacity",
												D.data.color * 0.009)
									} else {
										D.map.style.opacity = D.data.color * 0.009
									}
									return false
								},
								nodeMousemoveFn : function(H) {
									G = e.core.evt.fixEvent();
									w = G.layerX;
									u = G.layerY
								},
								clickSearchBtn : function(H) {
									var I = A.DOMs.searchForm[0];
									e.fireEvent(I, "submit");
									I.submit()
								},
								searchFormSubmit : function() {
									var H = " http://s.weibo.com/user/";
									H = H + A.DOM.searchInput.value
											+ "&auth=vip";
									A.DOM.searchForm.action = H
								},
								searchInputFocus : function() {
									if (A.DOM.searchInput.value != null) {
										A.DOM.searchInput.select()
									}
								},
								searchInputMouseUp : function() {
									e.stopEvent()
								}
							},
							comboData : function() {
								if (!$CONFIG || !$CONFIG.Group
										|| !$CONFIG.count || !$CONFIG.data) {
									e.log("请先加载$CONFIG, Group, count, data");
									return
								}
								var V = $CONFIG.Group;
								var S = {}, U, N;
								var W = V.provcodes.split(",");
								var H = V.provinces.split(",");
								var J = V.pinyin.split(",");
								var M = V.position.split(",");
								var R = $CONFIG.count;
								var P, L, T, I;
								for ( var O = 0, Q = W.length; O < Q; O++) {
									U = V["code" + W[O]].split(",");
									N = V["prov" + W[O]].split(",");
									
									P = S[W[O]] = {};
									L = [];
									T = Number(R[W[O]]);
									q = M[O].split("#");
									P.color = T > 100 ? 95 : T > 50 ? 60 : 35;
									P.province = H[O];
									P.count = U.length;
									P.posX = q[0];
									P.posY = q[1];
									if (e.inArray(H[O], a)) {
										for ( var K = 0; K < U.length; K++) {
											L.push({
												name : N[K],
												url : d + e.trim(J[O])
														+ "?_rdm=" + K
											})
										}
									} else {
										
										for ( var K = 0; K < U.length; K++) {
											L.push({
												name : N[K],
												url : d + '?area_id=' + W[O]
														+ "&city_id=" + U[K]
											})
										}
									}
									P.city = L;
									P.list = $CONFIG.data[W[O]]
								}
								return S
							},
							initMapData : function() {
								var J = A.comboData();
								if (!J) {
									e.log("数据初始化异常");
									return
								}
								for ( var I = 0, H = A.DOM.areas.length; I < H; I++) {
									k = A.DOM.areas[I];
									n = k.getAttribute("action-data")
											&& k.getAttribute("action-data")
													.split("=")[1];
									if (!J[n] || !A.DOM["area_" + n]) {
										continue
									}
									x[n] = {
										area : k,
										map : A.DOM["area_" + n],
										data : J[n],
										color : e.getStyle(k, "opacity") * 100,
										posX : J[n].posX,
										posY : J[n].posY
									};
									if (!h.IE9) {
										e.setStyle(A.DOM["area_" + n],
												"opacity",
												J[n]["color"] * 0.009)
									} else {
										A.DOM["area_" + n].style.opacity = J[n]["color"] * 0.009
									}
								}
							},
							renderPopup : function(O, L, J, area_id) {
								var K = "", N = "", I, H, M;
								if (L.list == undefined) return;

								
								for (I = 0; I < L.list.length; I++) {
									H = g.replace("$url", L.list[I].url);
									H = H.replace("$name", L.list[I].name);
									K += H
								}
								for (I = 0; I < L.city.length; I++) {
									H = c.replace("$url", L.city[I].url);
									H = H.replace("$name", L.city[I].name);
									N += H
								}

								e.addClassName(A.DOM.popSide, J ? "popup-lt"
										: "popup-rt");
								e.removeClassName(A.DOM.popSide, J ? "popup-rt"
										: "popup-lt");
								e.addClassName(A.DOM.popTop, J ? "popup-tp"
										: "popup-tpr");
								e.removeClassName(A.DOM.popTop, J ? "popup-tpr"
										: "popup-tp");
								e.addClassName(A.DOM.popBg, J ? "popup-in-bg"
										: "popup-inr-bg");
								e.removeClassName(A.DOM.popBg,
										J ? "popup-inr-bg" : "popup-in-bg");
								e.addClassName(A.DOM.popupBt, J ? "popup-bt"
										: "popup-btr");
								e.removeClassName(A.DOM.popupBt,
										J ? "popup-btr" : "popup-bt");
								
								var R = $CONFIG.count;
								var area_count = R[area_id];
//								
								var poptitle =  L.province + '&nbsp&nbsp<a href="/Sns/school_maps/get_school_list?area_id=' + area_id + '" target="_blank">' + area_count + '所学校</a>' ;
								
								
								A.DOM.popupTitle.innerHTML = poptitle;
								A.DOM.popupList.innerHTML = K;
								A.DOM.popupCity.innerHTML = N;
								A.DOM.popup.style.visibility = "hidden";
								A.DOM.popup.style.display = "block";
								M = e.core.dom.getSize(A.DOM.popupMain).height;
								A.DOM.popupIn.style.height = M + "px";
								A.DOM.popupBt.style.top = M + 8 + "px";
								A.DOM.popup.style.height = M + 16 + "px";
								A.DOM.popup.style.left = O.left + "px";
								A.DOM.popup.style.top = O.top + "px";
								A.DOM.popup.style.visibility = "visible"
							},
							hide : function(H) {
								A.DOM.popup.style.display = "none"
							},
							show : function(H) {
								var area_id = H.area.getAttribute("action-data").split("=")[1];
								if (!H) {
									if (A.DOM.popup.style.display != "none") {
										A.hide()
									}
									return
								}
								popupL = H.posX - 8;
								popupT = H.posY - 24;
								if (i - w < b) {
									isPopupInRight = false;
									popupL = popupL - b
								} else {
									isPopupInRight = true
								}
								A.renderPopup({
									left : popupL,
									top : popupT
								}, H.data, isPopupInRight, area_id)
							}
						};
						y = function() {
							if (!z) {
								throw new Error("node没有定义")
							}
						};
						l = function() {
							A.DOM = e.kit.dom.parseDOM(e.builder(z).list);
							A.DOM.areas = e.sizzle("area", A.DOM.fakelayerMap);
							A.DOMs = e.builder(z).list;
							A.initMapData();
							if (!1) {
								throw new Error("必需的节点 不存在")
							}
						};
						C = function() {
						};
						r = function() {
							e.addEvent(z, "mousemove",
									A.DOM_eventFun.nodeMousemoveFn);
							e.addEvent(A.DOM.searchInput, "focus",
									A.DOM_eventFun.searchInputFocus);
							e.addEvent(A.DOM.searchInput, "mouseup",
									A.DOM_eventFun.searchInputMouseUp);
							if (!h.IPAD) {
								p.add("areaAction", "mouseover",
										A.DOM_eventFun.areaActionMouseoverFn);
								p.add("areaAction", "mouseout",
										A.DOM_eventFun.areaActionMouseoutFn);
								e.addEvent(A.DOM.popup, "mouseover",
										A.DOM_eventFun.popMouseoverFn);
								e.addEvent(A.DOM.popup, "mouseout",
										A.DOM_eventFun.popMouseoutFn)
							} else {
								for ( var I = 0, H = A.DOM.areas.length; I < H; I++) {
									var J = A.DOM.areas[I].getAttribute(
											"action-data").split("=")[1];
									e.addEvent(A.DOM.areas[I], "click",
											(function(K) {
												return function() {
													A.show(K)
												}
											})(x[J]))
								}
							}
							e.addEvent(A.DOMs.searchBtn[0], "click",
									A.DOM_eventFun.clickSearchBtn);
							e.addEvent(A.DOM.searchForm, "submit",
									A.DOM_eventFun.searchFormSubmit)
						};
						t = function() {
						};
						v = function() {
						};
						F = function() {
							e.removeEvent(z, "mousemove",
									A.DOM_eventFun.nodeMousemoveFn);
							if (!e.core.util.browser.IPAD) {
								p.remove("areaAction", "mouseover",
										A.DOM_eventFun.areaActionMouseoverFn);
								p.remove("areaAction", "mouseout",
										A.DOM_eventFun.areaActionMouseoutFn);
								e.removeEvent(A.DOM.popup, "mouseover",
										A.DOM_eventFun.popMouseoverFn);
								e.removeEvent(A.DOM.popup, "mouseout",
										A.DOM_eventFun.popMouseoutFn)
							} else {
								for ( var I = 0, H = A.DOM.areas.length; I < H; I++) {
									var J = A.DOM.areas[I].getAttribute(
											"action-data").split("=")[1];
									e.removeEvent(A.DOM.areas[I], "click",
											(function(K) {
												return function() {
													A.show(K)
												}
											})(x[J]))
								}
							}
							if (A) {
								e.foreach(A.objs, function(K) {
									if (K.destroy) {
										K.destroy()
									}
								});
								A = null
							}
						};
						B = function() {
							y();
							l();
							C();
							r();
							t();
							v()
						};
						B();
						s.destroy = F;
						return s
					}
				});
STK.pageletM.register("pl.home.map", function(c) {
	var b = c.E("pl_home_map");
	var a = c.comp.home.map(b);
	return a
});
STK.pageletM.start();