var WBtopPublic = function() {
	var STK = function() {
		var a = {}, b = [];
		a.inc = function(a, b) {
			return !0
		};
		a.register = function(c, d) {
			var e = c.split("."), f = a, g = null;
			while (g = e.shift())
				if (e.length) {
					f[g] === undefined && (f[g] = {});
					f = f[g]
				} else if (f[g] === undefined)
					try {
						f[g] = d(a)
					} catch (h) {
						b.push(h)
					}
		};
		a.regShort = function(b, c) {
			if (a[b] !== undefined)
				throw "[" + b + "] : short : has been register";
			a[b] = c
		};
		a.IE = /msie/i.test(navigator.userAgent);
		a.E = function(a) {
			return typeof a == "string" ? document.getElementById(a) : a
		};
		a.C = function(a) {
			var b;
			a = a.toUpperCase();
			a == "TEXT" ? b = document.createTextNode("")
					: a == "BUFFER" ? b = document.createDocumentFragment()
							: b = document.createElement(a);
			return b
		};
		a.log = function(a) {
			b.push("[" + (new Date).getTime() % 1e5 + "]: " + a)
		};
		a.getErrorLogInformationList = function(a) {
			return b.splice(0, a || b.length)
		};
		return a
	}();
	$Import = STK.inc;
	STK.register("core.evt.addEvent", function(a) {
		return function(b, c, d) {
			var e = a.E(b);
			if (e == null)
				return !1;
			c = c || "click";
			if ((typeof d).toLowerCase() == "function") {
				e.addEventListener ? e.addEventListener(c, d, !1)
						: e.attachEvent ? e.attachEvent("on" + c, d) : e["on"
								+ c] = d;
				return !0
			}
		}
	});
	STK.register("core.evt.removeEvent", function(a) {
		return function(b, c, d, e) {
			var f = a.E(b);
			if (f == null)
				return !1;
			if (typeof d != "function")
				return !1;
			f.removeEventListener ? f.removeEventListener(c, d, e)
					: f.detachEvent ? f.detachEvent("on" + c, d)
							: f["on" + c] = null;
			return !0
		}
	});
	STK
			.register(
					"core.util.browser",
					function(a) {
						var b = navigator.userAgent.toLowerCase(), c = window.external
								|| "", d, e, f, g, h, i = function(a) {
							var b = 0;
							return parseFloat(a.replace(/\./g, function() {
								return b++ == 1 ? "" : "."
							}))
						};
						try {
							/windows|win32/i.test(b) ? h = "windows"
									: /macintosh/i.test(b) ? h = "macintosh"
											: /rhino/i.test(b) && (h = "rhino");
							if ((e = b.match(/applewebkit\/([^\s]*)/)) && e[1]) {
								d = "webkit";
								g = i(e[1])
							} else if ((e = b.match(/presto\/([\d.]*)/))
									&& e[1]) {
								d = "presto";
								g = i(e[1])
							} else if (e = b.match(/msie\s([^;]*)/)) {
								d = "trident";
								g = 1;
								(e = b.match(/trident\/([\d.]*)/)) && e[1]
										&& (g = i(e[1]))
							} else if (/gecko/.test(b)) {
								d = "gecko";
								g = 1;
								(e = b.match(/rv:([\d.]*)/)) && e[1]
										&& (g = i(e[1]))
							}
							/world/.test(b) ? f = "world"
									: /360se/.test(b) ? f = "360"
											: /maxthon/.test(b)
													|| typeof c.max_version == "number" ? f = "maxthon"
													: /tencenttraveler\s([\d.]*)/
															.test(b) ? f = "tt"
															: /se\s([\d.]*)/
																	.test(b)
																	&& (f = "sogou")
						} catch (j) {
						}
						var k = {
							OS : h,
							CORE : d,
							Version : g,
							EXTRA : f ? f : !1,
							IE : /msie/.test(b),
							OPERA : /opera/.test(b),
							MOZ : /gecko/.test(b)
									&& !/(compatible|webkit)/.test(b),
							IE5 : /msie 5 /.test(b),
							IE55 : /msie 5.5/.test(b),
							IE6 : /msie 6/.test(b),
							IE7 : /msie 7/.test(b),
							IE8 : /msie 8/.test(b),
							IE9 : /msie 9/.test(b),
							SAFARI : !/chrome\/([\d.]*)/.test(b)
									&& /\/([\d.]*) safari/.test(b),
							CHROME : /chrome\/([\d.]*)/.test(b),
							IPAD : /\(ipad/i.test(b),
							IPHONE : /\(iphone/i.test(b),
							ITOUCH : /\(itouch/i.test(b),
							MOBILE : /mobile/i.test(b)
						};
						return k
					});
	STK.register("core.evt.getEvent", function(a) {
		return function() {
			if (a.IE)
				return window.event;
			if (window.event)
				return window.event;
			var b = arguments.callee.caller, c, d = 0;
			while (b != null && d < 40) {
				c = b.arguments[0];
				if (!(!c || c.constructor != Event
						&& c.constructor != MouseEvent
						&& c.constructor != KeyboardEvent))
					return c;
				d++;
				b = b.caller
			}
			return c
		}
	});
	STK.register("core.evt.fixEvent", function(a) {
		return function(b) {
			b = b || a.core.evt.getEvent();
			if (!b.target) {
				b.target = b.srcElement;
				b.pageX = b.x;
				b.pageY = b.y
			}
			typeof b.layerX == "undefined" && (b.layerX = b.offsetX);
			typeof b.layerY == "undefined" && (b.layerY = b.offsetY);
			return b
		}
	});
	STK.register("core.evt.preventDefault", function(a) {
		return function(b) {
			var c = b ? b : a.core.evt.getEvent();
			a.IE ? c.returnValue = !1 : c.preventDefault()
		}
	});
	STK.register("core.arr.isArray", function(a) {
		return function(a) {
			return Object.prototype.toString.call(a) === "[object Array]"
		}
	});
	STK.register("core.str.trim", function(a) {
		return function(a) {
			if (typeof a != "string")
				throw "trim need a string as parameter";
			var b = a.length, c = 0, d = /(\u3000|\s|\t|\u00A0)/;
			while (c < b) {
				if (!d.test(a.charAt(c)))
					break;
				c += 1
			}
			while (b > c) {
				if (!d.test(a.charAt(b - 1)))
					break;
				b -= 1
			}
			return a.slice(c, b)
		}
	});
	STK.register("core.json.queryToJson", function(a) {
		return function(b, c) {
			var d = a.core.str.trim(b).split("&"), e = {}, f = function(a) {
				return c ? decodeURIComponent(a) : a
			};
			for ( var g = 0, h = d.length; g < h; g++)
				if (d[g]) {
					var i = d[g].split("="), j = i[0], k = i[1];
					if (i.length < 2) {
						k = j;
						j = "$nullName"
					}
					if (!e[j])
						e[j] = f(k);
					else {
						a.core.arr.isArray(e[j]) != !0 && (e[j] = [ e[j] ]);
						e[j].push(f(k))
					}
				}
			return e
		}
	});
	STK.register("core.dom.isNode", function(a) {
		return function(a) {
			return a != undefined && Boolean(a.nodeName) && Boolean(a.nodeType)
		}
	});
	STK
			.register(
					"core.dom.sizzle",
					function(a) {
						function p(a, b, c, d, e, f) {
							for ( var h = 0, i = d.length; h < i; h++) {
								var j = d[h];
								if (j) {
									j = j[a];
									var k = !1;
									while (j) {
										if (j.sizcache === c) {
											k = d[j.sizset];
											break
										}
										if (j.nodeType === 1) {
											if (!f) {
												j.sizcache = c;
												j.sizset = h
											}
											if (typeof b != "string") {
												if (j === b) {
													k = !0;
													break
												}
											} else if (g.filter(b, [ j ]).length > 0) {
												k = j;
												break
											}
										}
										j = j[a]
									}
									d[h] = k
								}
							}
						}
						function o(a, b, c, d, e, f) {
							for ( var g = 0, h = d.length; g < h; g++) {
								var i = d[g];
								if (i) {
									i = i[a];
									var j = !1;
									while (i) {
										if (i.sizcache === c) {
											j = d[i.sizset];
											break
										}
										if (i.nodeType === 1 && !f) {
											i.sizcache = c;
											i.sizset = g
										}
										if (i.nodeName.toLowerCase() === b) {
											j = i;
											break
										}
										i = i[a]
									}
									d[g] = j
								}
							}
						}
						var b = /((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g, c = 0, d = Object.prototype.toString, e = !1, f = !0;
						[ 0, 0 ].sort(function() {
							f = !1;
							return 0
						});
						var g = function(a, c, e, f) {
							e = e || [];
							c = c || document;
							var j = c;
							if (c.nodeType !== 1 && c.nodeType !== 9)
								return [];
							if (!a || typeof a != "string")
								return e;
							var k = [], m, n, o, p, r = !0, s = g.isXML(c), t = a, u, v, w, x;
							do {
								b.exec("");
								m = b.exec(t);
								if (m) {
									t = m[3];
									k.push(m[1]);
									if (m[2]) {
										p = m[3];
										break
									}
								}
							} while (m);
							if (k.length > 1 && i.exec(a))
								if (k.length === 2 && h.relative[k[0]])
									n = q(k[0] + k[1], c);
								else {
									n = h.relative[k[0]] ? [ c ] : g(k.shift(),
											c);
									while (k.length) {
										a = k.shift();
										h.relative[a] && (a += k.shift());
										n = q(a, n)
									}
								}
							else {
								if (!f && k.length > 1 && c.nodeType === 9
										&& !s && h.match.ID.test(k[0])
										&& !h.match.ID.test(k[k.length - 1])) {
									u = g.find(k.shift(), c, s);
									c = u.expr ? g.filter(u.expr, u.set)[0]
											: u.set[0]
								}
								if (c) {
									u = f ? {
										expr : k.pop(),
										set : l(f)
									} : g.find(k.pop(), k.length === 1
											&& (k[0] === "~" || k[0] === "+")
											&& c.parentNode ? c.parentNode : c,
											s);
									n = u.expr ? g.filter(u.expr, u.set)
											: u.set;
									k.length > 0 ? o = l(n) : r = !1;
									while (k.length) {
										v = k.pop();
										w = v;
										h.relative[v] ? w = k.pop() : v = "";
										w == null && (w = c);
										h.relative[v](o, w, s)
									}
								} else
									o = k = []
							}
							o || (o = n);
							o || g.error(v || a);
							if (d.call(o) === "[object Array]")
								if (!r)
									e.push.apply(e, o);
								else if (c && c.nodeType === 1)
									for (x = 0; o[x] != null; x++)
										o[x]
												&& (o[x] === !0 || o[x].nodeType === 1
														&& g.contains(c, o[x]))
												&& e.push(n[x]);
								else
									for (x = 0; o[x] != null; x++)
										o[x] && o[x].nodeType === 1
												&& e.push(n[x]);
							else
								l(o, e);
							if (p) {
								g(p, j, e, f);
								g.uniqueSort(e)
							}
							return e
						};
						g.uniqueSort = function(a) {
							if (n) {
								e = f;
								a.sort(n);
								if (e)
									for ( var b = 1; b < a.length; b++)
										a[b] === a[b - 1] && a.splice(b--, 1)
							}
							return a
						};
						g.matches = function(a, b) {
							return g(a, null, null, b)
						};
						g.find = function(a, b, c) {
							var d;
							if (!a)
								return [];
							for ( var e = 0, f = h.order.length; e < f; e++) {
								var g = h.order[e], i;
								if (i = h.leftMatch[g].exec(a)) {
									var j = i[1];
									i.splice(1, 1);
									if (j.substr(j.length - 1) !== "\\") {
										i[1] = (i[1] || "").replace(/\\/g, "");
										d = h.find[g](i, b, c);
										if (d != null) {
											a = a.replace(h.match[g], "");
											break
										}
									}
								}
							}
							d || (d = b.getElementsByTagName("*"));
							return {
								set : d,
								expr : a
							}
						};
						g.filter = function(a, b, c, d) {
							var e = a, f = [], i = b, j, k, l = b && b[0]
									&& g.isXML(b[0]);
							while (a && b.length) {
								for ( var m in h.filter)
									if ((j = h.leftMatch[m].exec(a)) != null
											&& j[2]) {
										var n = h.filter[m], o, p, q = j[1];
										k = !1;
										j.splice(1, 1);
										if (q.substr(q.length - 1) === "\\")
											continue;
										i === f && (f = []);
										if (h.preFilter[m]) {
											j = h.preFilter[m]
													(j, i, c, f, d, l);
											if (!j)
												k = o = !0;
											else if (j === !0)
												continue
										}
										if (j)
											for ( var r = 0; (p = i[r]) != null; r++)
												if (p) {
													o = n(p, j, r, i);
													var s = d ^ !!o;
													if (c && o != null)
														s ? k = !0 : i[r] = !1;
													else if (s) {
														f.push(p);
														k = !0
													}
												}
										if (o !== undefined) {
											c || (i = f);
											a = a.replace(h.match[m], "");
											if (!k)
												return [];
											break
										}
									}
								if (a === e)
									if (k == null)
										g.error(a);
									else
										break;
								e = a
							}
							return i
						};
						g.error = function(a) {
							throw "Syntax error, unrecognized expression: " + a
						};
						var h = {
							order : [ "ID", "NAME", "TAG" ],
							match : {
								ID : /#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
								CLASS : /\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,
								NAME : /\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,
								ATTR : /\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,
								TAG : /^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,
								CHILD : /:(only|nth|last|first)-child(?:\((even|odd|[\dn+\-]*)\))?/,
								POS : /:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,
								PSEUDO : /:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/
							},
							leftMatch : {},
							attrMap : {
								"class" : "className",
								"for" : "htmlFor"
							},
							attrHandle : {
								href : function(a) {
									return a.getAttribute("href")
								}
							},
							relative : {
								"+" : function(a, b) {
									var c = typeof b == "string", d = c
											&& !/\W/.test(b), e = c && !d;
									d && (b = b.toLowerCase());
									for ( var f = 0, h = a.length, i; f < h; f++)
										if (i = a[f]) {
											while ((i = i.previousSibling)
													&& i.nodeType !== 1)
												;
											a[f] = e
													|| i
													&& i.nodeName.toLowerCase() === b ? i
													|| !1
													: i === b
										}
									e && g.filter(b, a, !0)
								},
								">" : function(a, b) {
									var c = typeof b == "string", d, e = 0, f = a.length;
									if (c && !/\W/.test(b)) {
										b = b.toLowerCase();
										for (; e < f; e++) {
											d = a[e];
											if (d) {
												var h = d.parentNode;
												a[e] = h.nodeName.toLowerCase() === b ? h
														: !1
											}
										}
									} else {
										for (; e < f; e++) {
											d = a[e];
											d
													&& (a[e] = c ? d.parentNode
															: d.parentNode === b)
										}
										c && g.filter(b, a, !0)
									}
								},
								"" : function(a, b, d) {
									var e = c++, f = p, g;
									if (typeof b == "string" && !/\W/.test(b)) {
										b = b.toLowerCase();
										g = b;
										f = o
									}
									f("parentNode", b, e, a, g, d)
								},
								"~" : function(a, b, d) {
									var e = c++, f = p, g;
									if (typeof b == "string" && !/\W/.test(b)) {
										b = b.toLowerCase();
										g = b;
										f = o
									}
									f("previousSibling", b, e, a, g, d)
								}
							},
							find : {
								ID : function(a, b, c) {
									if (typeof b.getElementById != "undefined"
											&& !c) {
										var d = b.getElementById(a[1]);
										return d ? [ d ] : []
									}
								},
								NAME : function(a, b) {
									if (typeof b.getElementsByName != "undefined") {
										var c = [], d = b
												.getElementsByName(a[1]);
										for ( var e = 0, f = d.length; e < f; e++)
											d[e].getAttribute("name") === a[1]
													&& c.push(d[e]);
										return c.length === 0 ? null : c
									}
								},
								TAG : function(a, b) {
									return b.getElementsByTagName(a[1])
								}
							},
							preFilter : {
								CLASS : function(a, b, c, d, e, f) {
									a = " " + a[1].replace(/\\/g, "") + " ";
									if (f)
										return a;
									for ( var g = 0, h; (h = b[g]) != null; g++)
										h
												&& (e
														^ (h.className && (" "
																+ h.className + " ")
																.replace(
																		/[\t\n]/g,
																		" ")
																.indexOf(a) >= 0) ? c
														|| d.push(h)
														: c && (b[g] = !1));
									return !1
								},
								ID : function(a) {
									return a[1].replace(/\\/g, "")
								},
								TAG : function(a, b) {
									return a[1].toLowerCase()
								},
								CHILD : function(a) {
									if (a[1] === "nth") {
										var b = /(-?)(\d*)n((?:\+|-)?\d*)/
												.exec(a[2] === "even" && "2n"
														|| a[2] === "odd"
														&& "2n+1"
														|| !/\D/.test(a[2])
														&& "0n+" + a[2] || a[2]);
										a[2] = b[1] + (b[2] || 1) - 0;
										a[3] = b[3] - 0
									}
									a[0] = c++;
									return a
								},
								ATTR : function(a, b, c, d, e, f) {
									var g = a[1].replace(/\\/g, "");
									!f && h.attrMap[g] && (a[1] = h.attrMap[g]);
									a[2] === "~=" && (a[4] = " " + a[4] + " ");
									return a
								},
								PSEUDO : function(a, c, d, e, f) {
									if (a[1] === "not")
										if ((b.exec(a[3]) || "").length > 1
												|| /^\w/.test(a[3]))
											a[3] = g(a[3], null, null, c);
										else {
											var i = g
													.filter(a[3], c, d, !0 ^ f);
											d || e.push.apply(e, i);
											return !1
										}
									else if (h.match.POS.test(a[0])
											|| h.match.CHILD.test(a[0]))
										return !0;
									return a
								},
								POS : function(a) {
									a.unshift(!0);
									return a
								}
							},
							filters : {
								enabled : function(a) {
									return a.disabled === !1
											&& a.type !== "hidden"
								},
								disabled : function(a) {
									return a.disabled === !0
								},
								checked : function(a) {
									return a.checked === !0
								},
								selected : function(a) {
									a.parentNode.selectedIndex;
									return a.selected === !0
								},
								parent : function(a) {
									return !!a.firstChild
								},
								empty : function(a) {
									return !a.firstChild
								},
								has : function(a, b, c) {
									return !!g(c[3], a).length
								},
								header : function(a) {
									return /h\d/i.test(a.nodeName)
								},
								text : function(a) {
									return "text" === a.type
								},
								radio : function(a) {
									return "radio" === a.type
								},
								checkbox : function(a) {
									return "checkbox" === a.type
								},
								file : function(a) {
									return "file" === a.type
								},
								password : function(a) {
									return "password" === a.type
								},
								submit : function(a) {
									return "submit" === a.type
								},
								image : function(a) {
									return "image" === a.type
								},
								reset : function(a) {
									return "reset" === a.type
								},
								button : function(a) {
									return "button" === a.type
											|| a.nodeName.toLowerCase() === "button"
								},
								input : function(a) {
									return /input|select|textarea|button/i
											.test(a.nodeName)
								}
							},
							setFilters : {
								first : function(a, b) {
									return b === 0
								},
								last : function(a, b, c, d) {
									return b === d.length - 1
								},
								even : function(a, b) {
									return b % 2 === 0
								},
								odd : function(a, b) {
									return b % 2 === 1
								},
								lt : function(a, b, c) {
									return b < c[3] - 0
								},
								gt : function(a, b, c) {
									return b > c[3] - 0
								},
								nth : function(a, b, c) {
									return c[3] - 0 === b
								},
								eq : function(a, b, c) {
									return c[3] - 0 === b
								}
							},
							filter : {
								PSEUDO : function(a, b, c, d) {
									var e = b[1], f = h.filters[e];
									if (f)
										return f(a, c, b, d);
									if (e === "contains")
										return (a.textContent || a.innerText
												|| g.getText([ a ]) || "")
												.indexOf(b[3]) >= 0;
									if (e === "not") {
										var i = b[3];
										for ( var j = 0, k = i.length; j < k; j++)
											if (i[j] === a)
												return !1;
										return !0
									}
									g
											.error("Syntax error, unrecognized expression: "
													+ e)
								},
								CHILD : function(a, b) {
									var c = b[1], d = a;
									switch (c) {
									case "only":
									case "first":
										while (d = d.previousSibling)
											if (d.nodeType === 1)
												return !1;
										if (c === "first")
											return !0;
										d = a;
									case "last":
										while (d = d.nextSibling)
											if (d.nodeType === 1)
												return !1;
										return !0;
									case "nth":
										var e = b[2], f = b[3];
										if (e === 1 && f === 0)
											return !0;
										var g = b[0], h = a.parentNode;
										if (h
												&& (h.sizcache !== g || !a.nodeIndex)) {
											var i = 0;
											for (d = h.firstChild; d; d = d.nextSibling)
												d.nodeType === 1
														&& (d.nodeIndex = ++i);
											h.sizcache = g
										}
										var j = a.nodeIndex - f;
										return e === 0 ? j === 0 : j % e === 0
												&& j / e >= 0
									}
								},
								ID : function(a, b) {
									return a.nodeType === 1
											&& a.getAttribute("id") === b
								},
								TAG : function(a, b) {
									return b === "*" && a.nodeType === 1
											|| a.nodeName.toLowerCase() === b
								},
								CLASS : function(a, b) {
									return (" "
											+ (a.className || a
													.getAttribute("class")) + " ")
											.indexOf(b) > -1
								},
								ATTR : function(a, b) {
									var c = b[1], d = h.attrHandle[c] ? h.attrHandle[c]
											(a)
											: a[c] != null ? a[c] : a
													.getAttribute(c), e = d
											+ "", f = b[2], g = b[4];
									return d == null ? f === "!="
											: f === "=" ? e === g
													: f === "*=" ? e.indexOf(g) >= 0
															: f === "~=" ? (" "
																	+ e + " ")
																	.indexOf(g) >= 0
																	: g ? f === "!=" ? e !== g
																			: f === "^=" ? e
																					.indexOf(g) === 0
																					: f === "$=" ? e
																							.substr(e.length
																									- g.length) === g
																							: f === "|=" ? e === g
																									|| e
																											.substr(
																													0,
																													g.length + 1) === g
																											+ "-"
																									: !1
																			: e
																					&& d !== !1
								},
								POS : function(a, b, c, d) {
									var e = b[2], f = h.setFilters[e];
									if (f)
										return f(a, c, b, d)
								}
							}
						};
						g.selectors = h;
						var i = h.match.POS, j = function(a, b) {
							return "\\" + (b - 0 + 1)
						};
						for ( var k in h.match) {
							h.match[k] = new RegExp(h.match[k].source
									+ /(?![^\[]*\])(?![^\(]*\))/.source);
							h.leftMatch[k] = new RegExp(
									/(^(?:.|\r|\n)*?)/.source
											+ h.match[k].source.replace(
													/\\(\d+)/g, j))
						}
						var l = function(a, b) {
							a = Array.prototype.slice.call(a, 0);
							if (b) {
								b.push.apply(b, a);
								return b
							}
							return a
						};
						try {
							Array.prototype.slice.call(
									document.documentElement.childNodes, 0)[0].nodeType
						} catch (m) {
							l = function(a, b) {
								var c = b || [], e = 0;
								if (d.call(a) === "[object Array]")
									Array.prototype.push.apply(c, a);
								else if (typeof a.length == "number")
									for ( var f = a.length; e < f; e++)
										c.push(a[e]);
								else
									for (; a[e]; e++)
										c.push(a[e]);
								return c
							}
						}
						var n;
						document.documentElement.compareDocumentPosition ? n = function(
								a, b) {
							if (!a.compareDocumentPosition
									|| !b.compareDocumentPosition) {
								a == b && (e = !0);
								return a.compareDocumentPosition ? -1 : 1
							}
							var c = a.compareDocumentPosition(b) & 4 ? -1
									: a === b ? 0 : 1;
							c === 0 && (e = !0);
							return c
						}
								: "sourceIndex" in document.documentElement ? n = function(
										a, b) {
									if (!a.sourceIndex || !b.sourceIndex) {
										a == b && (e = !0);
										return a.sourceIndex ? -1 : 1
									}
									var c = a.sourceIndex - b.sourceIndex;
									c === 0 && (e = !0);
									return c
								}
										: document.createRange
												&& (n = function(a, b) {
													if (!a.ownerDocument
															|| !b.ownerDocument) {
														a == b && (e = !0);
														return a.ownerDocument ? -1
																: 1
													}
													var c = a.ownerDocument
															.createRange(), d = b.ownerDocument
															.createRange();
													c.setStart(a, 0);
													c.setEnd(a, 0);
													d.setStart(b, 0);
													d.setEnd(b, 0);
													var f = c
															.compareBoundaryPoints(
																	Range.START_TO_END,
																	d);
													f === 0 && (e = !0);
													return f
												});
						g.getText = function(a) {
							var b = "", c;
							for ( var d = 0; a[d]; d++) {
								c = a[d];
								c.nodeType === 3 || c.nodeType === 4 ? b += c.nodeValue
										: c.nodeType !== 8
												&& (b += g
														.getText(c.childNodes))
							}
							return b
						};
						(function() {
							var a = document.createElement("div"), b = "script"
									+ (new Date).getTime();
							a.innerHTML = "<a name='" + b + "'/>";
							var c = document.documentElement;
							c.insertBefore(a, c.firstChild);
							if (document.getElementById(b)) {
								h.find.ID = function(a, b, c) {
									if (typeof b.getElementById != "undefined"
											&& !c) {
										var d = b.getElementById(a[1]);
										return d ? d.id === a[1]
												|| typeof d.getAttributeNode != "undefined"
												&& d.getAttributeNode("id").nodeValue === a[1] ? [ d ]
												: undefined
												: []
									}
								};
								h.filter.ID = function(a, b) {
									var c = typeof a.getAttributeNode != "undefined"
											&& a.getAttributeNode("id");
									return a.nodeType === 1 && c
											&& c.nodeValue === b
								}
							}
							c.removeChild(a);
							c = a = null
						})();
						(function() {
							var a = document.createElement("div");
							a.appendChild(document.createComment(""));
							a.getElementsByTagName("*").length > 0
									&& (h.find.TAG = function(a, b) {
										var c = b.getElementsByTagName(a[1]);
										if (a[1] === "*") {
											var d = [];
											for ( var e = 0; c[e]; e++)
												c[e].nodeType === 1
														&& d.push(c[e]);
											c = d
										}
										return c
									});
							a.innerHTML = "<a href='#'></a>";
							a.firstChild
									&& typeof a.firstChild.getAttribute != "undefined"
									&& a.firstChild.getAttribute("href") !== "#"
									&& (h.attrHandle.href = function(a) {
										return a.getAttribute("href", 2)
									});
							a = null
						})();
						document.querySelectorAll
								&& function() {
									var a = g, b = document
											.createElement("div");
									b.innerHTML = "<p class='TEST'></p>";
									if (!b.querySelectorAll
											|| b.querySelectorAll(".TEST").length !== 0) {
										g = function(b, c, d, e) {
											c = c || document;
											if (!e && c.nodeType === 9
													&& !g.isXML(c))
												try {
													return l(
															c
																	.querySelectorAll(b),
															d)
												} catch (f) {
												}
											return a(b, c, d, e)
										};
										for ( var c in a)
											g[c] = a[c];
										b = null
									}
								}();
						(function() {
							var a = document.createElement("div");
							a.innerHTML = "<div class='test e'></div><div class='test'></div>";
							if (!!a.getElementsByClassName
									&& a.getElementsByClassName("e").length !== 0) {
								a.lastChild.className = "e";
								if (a.getElementsByClassName("e").length === 1)
									return;
								h.order.splice(1, 0, "CLASS");
								h.find.CLASS = function(a, b, c) {
									if (typeof b.getElementsByClassName != "undefined"
											&& !c)
										return b.getElementsByClassName(a[1])
								};
								a = null
							}
						})();
						g.contains = document.compareDocumentPosition ? function(
								a, b) {
							return !!(a.compareDocumentPosition(b) & 16)
						}
								: function(a, b) {
									return a !== b
											&& (a.contains ? a.contains(b) : !0)
								};
						g.isXML = function(a) {
							var b = (a ? a.ownerDocument || a : 0).documentElement;
							return b ? b.nodeName !== "HTML" : !1
						};
						var q = function(a, b) {
							var c = [], d = "", e, f = b.nodeType ? [ b ] : b;
							while (e = h.match.PSEUDO.exec(a)) {
								d += e[0];
								a = a.replace(h.match.PSEUDO, "")
							}
							a = h.relative[a] ? a + "*" : a;
							for ( var i = 0, j = f.length; i < j; i++)
								g(a, f[i], c);
							return g.filter(d, c)
						};
						return g
					});
	STK.register("core.dom.contains", function(a) {
		return function(a, b) {
			if (a === b)
				return !1;
			if (a.compareDocumentPosition)
				return (a.compareDocumentPosition(b) & 16) === 16;
			if (a.contains && b.nodeType === 1)
				return a.contains(b);
			while (b = b.parentNode)
				if (a === b)
					return !0;
			return !1
		}
	});
	STK.register("core.obj.isEmpty", function(a) {
		return function(a, b) {
			var c = !0;
			for ( var d in a) {
				if (b) {
					c = !1;
					break
				}
				if (a.hasOwnProperty(d)) {
					c = !1;
					break
				}
			}
			return c
		}
	});
	STK.register("core.func.empty", function() {
		return function() {
		}
	});
	STK
			.register(
					"core.evt.delegatedEvent",
					function(a) {
						var b = function(b, c) {
							for ( var d = 0, e = b.length; d < e; d += 1)
								if (a.core.dom.contains(b[d], c))
									return !0;
							return !1
						};
						return function(c, d) {
							if (!a.core.dom.isNode(c))
								throw "core.evt.delegatedEvent need an Element as first Parameter";
							d || (d = []);
							a.core.arr.isArray(d) && (d = [ d ]);
							var e = {}, f = function(f) {
								var g = a.core.evt.fixEvent(f), h = g.target, i = f.type, j = function() {
									var b, d, e;
									b = h.getAttribute("action-target");
									if (b) {
										d = a.core.dom.sizzle(b, c);
										d.length && (e = g.target = d[0])
									}
									j = a.core.func.empty;
									return e
								}, k = function() {
									var b = j() || h;
									return e[i] && e[i][l] ? e[i][l]({
										evt : g,
										el : b,
										box : c,
										data : a.core.json.queryToJson(b
												.getAttribute("action-data")
												|| "")
									}) : !0
								};
								if (b(d, h))
									return !1;
								if (!a.core.dom.contains(c, h))
									return !1;
								var l = null;
								while (h && h !== c) {
									l = h.getAttribute("action-type");
									if (l && k() === !1)
										break;
									h = h.parentNode
								}
							}, g = {};
							g.add = function(b, d, g) {
								if (!e[d]) {
									e[d] = {};
									a.core.evt.addEvent(c, d, f)
								}
								var h = e[d];
								h[b] = g
							};
							g.remove = function(b, d) {
								if (e[d]) {
									delete e[d][b];
									if (a.core.obj.isEmpty(e[d])) {
										delete e[d];
										a.core.evt.removeEvent(c, d, f)
									}
								}
							};
							g.pushExcept = function(a) {
								d.push(a)
							};
							g.removeExcept = function(a) {
								if (!a)
									d = [];
								else
									for ( var b = 0, c = d.length; b < c; b += 1)
										d[b] === a && d.splice(b, 1)
							};
							g.clearExcept = function(a) {
								d = []
							};
							g.destroy = function() {
								for (k in e) {
									for (l in e[k])
										delete e[k][l];
									delete e[k];
									a.core.evt.removeEvent(c, k, f)
								}
							};
							return g
						}
					});
	STK.register("core.evt.stopEvent", function(a) {
		return function(b) {
			var c = b ? b : a.core.evt.getEvent();
			if (a.IE) {
				c.cancelBubble = !0;
				c.returnValue = !1
			} else {
				c.preventDefault();
				c.stopPropagation()
			}
			return !1
		}
	});
	STK.register("core.dom.hasClassName", function(a) {
		return function(a, b) {
			return (new RegExp("\\b" + b + "\\b")).test(a.className)
		}
	});
	STK.register("core.dom.addClassName", function(a) {
		return function(b, c) {
			b.nodeType === 1
					&& (a.core.dom.hasClassName(b, c) || (b.className += " "
							+ c))
		}
	});
	STK.register("core.dom.removeClassName", function(a) {
		return function(b, c) {
			b.nodeType === 1
					&& a.core.dom.hasClassName(b, c)
					&& (b.className = b.className.replace(new RegExp("\\b" + c
							+ "\\b"), " "))
		}
	});
	STK.register("core.util.scrollPos", function(a) {
		return function(a) {
			a = a || document;
			var b = a.documentElement, c = a.body;
			return {
				top : Math.max(window.pageYOffset || 0, b.scrollTop,
						c.scrollTop),
				left : Math.max(window.pageXOffset || 0, b.scrollLeft,
						c.scrollLeft)
			}
		}
	});
	STK.register("core.obj.parseParam", function(a) {
		return function(a, b, c) {
			var d, e = {};
			b = b || {};
			for (d in a) {
				e[d] = a[d];
				b[d] != null
						&& (c ? a.hasOwnProperty[d] && (e[d] = b[d])
								: e[d] = b[d])
			}
			return e
		}
	});
	STK.register("core.dom.position", function(a) {
		var b = function(b) {
			var c, d, e, f, g, h;
			c = b.getBoundingClientRect();
			d = a.core.util.scrollPos();
			e = b.ownerDocument.body;
			f = b.ownerDocument.documentElement;
			g = f.clientTop || e.clientTop || 0;
			h = f.clientLeft || e.clientLeft || 0;
			return {
				l : parseInt(c.left + d.left - h, 10) || 0,
				t : parseInt(c.top + d.top - g, 10) || 0
			}
		}, c = function(b, c) {
			var d;
			d = [ b.offsetLeft, b.offsetTop ];
			parent = b.offsetParent;
			if (parent !== b && parent !== c)
				while (parent) {
					d[0] += parent.offsetLeft;
					d[1] += parent.offsetTop;
					parent = parent.offsetParent
				}
			if (a.core.util.browser.OPERA != -1
					|| a.core.util.browser.SAFARI != -1
					&& b.style.position == "absolute") {
				d[0] -= document.body.offsetLeft;
				d[1] -= document.body.offsetTop
			}
			b.parentNode ? parent = b.parentNode : parent = null;
			while (parent && !/^body|html$/i.test(parent.tagName)
					&& parent !== c) {
				if (parent.style.display.search(/^inline|table-row.*$/i)) {
					d[0] -= parent.scrollLeft;
					d[1] -= parent.scrollTop
				}
				parent = parent.parentNode
			}
			return {
				l : parseInt(d[0], 10),
				t : parseInt(d[1], 10)
			}
		};
		return function(d, e) {
			if (d == document.body)
				return !1;
			if (d.parentNode == null)
				return !1;
			if (d.style.display == "none")
				return !1;
			var f = a.core.obj.parseParam({
				parent : null
			}, e);
			if (d.getBoundingClientRect) {
				if (f.parent) {
					var g = b(d), h = b(f.parent);
					return {
						l : g.l - h.l,
						t : g.t - h.t
					}
				}
				return b(d)
			}
			return c(d, f.parent || document.body)
		}
	});
	STK.register("core.arr.indexOf", function(a) {
		return function(a, b) {
			if (b.indexOf)
				return b.indexOf(a);
			for ( var c = 0, d = b.length; c < d; c++)
				if (b[c] === a)
					return c;
			return -1
		}
	});
	STK.register("core.arr.inArray", function(a) {
		return function(b, c) {
			return a.core.arr.indexOf(b, c) > -1
		}
	});
	STK.register("core.func.getType", function(a) {
		return function(a) {
			var b;
			return ((b = typeof a) == "object" ? a == null && "null"
					|| Object.prototype.toString.call(a).slice(8, -1) : b)
					.toLowerCase()
		}
	});
	STK
			.register(
					"core.dom.builder",
					function(a) {
						function b(b, c) {
							if (c)
								return c;
							var d, e = /\<(\w+)[^>]*\s+node-type\s*=\s*([\'\"])?(\w+)\2.*?>/g, f = {}, g, h, i;
							while (d = e.exec(b)) {
								h = d[1];
								g = d[3];
								i = h + "[node-type=" + g + "]";
								f[g] = f[g] == null ? [] : f[g];
								a.core.arr.inArray(i, f[g])
										|| f[g].push(h + "[node-type=" + g
												+ "]")
							}
							return f
						}
						return function(c, d) {
							var e = a.core.func.getType(c) == "string", f = b(
									e ? c : c.innerHTML, d), g = c;
							if (e) {
								g = a.C("div");
								g.innerHTML = c
							}
							var h, i, j;
							j = a.core.dom.sizzle("[node-type]", g);
							i = {};
							for (h in f)
								i[h] = a.core.dom.sizzle.matches(f[h]
										.toString(), j);
							var k = c;
							if (e) {
								k = a.C("buffer");
								while (g.children[0])
									k.appendChild(g.children[0])
							}
							return {
								box : k,
								list : i
							}
						}
					});
	STK.register("core.str.bLength", function(a) {
		return function(a) {
			if (!a)
				return 0;
			var b = a.match(/[^\x00-\xff]/g);
			return a.length + (b ? b.length : 0)
		}
	});
	STK.register("core.str.leftB", function(a) {
		return function(b, c) {
			var d = b.replace(/\*/g, " ").replace(/[^\x00-\xff]/g, "**");
			b = b.slice(0, d.slice(0, c).replace(/\*\*/g, " ").replace(/\*/g,
					"").length);
			a.core.str.bLength(b) > c && c > 0
					&& (b = b.slice(0, b.length - 1));
			return b
		}
	});
	STK.register("core.dom.removeNode", function(a) {
		return function(b) {
			b = a.E(b) || b;
			try {
				b.parentNode.removeChild(b)
			} catch (c) {
			}
		}
	});
	STK.register("core.util.getUniqueKey", function(a) {
		var b = (new Date).getTime().toString(), c = 1;
		return function() {
			return b + c++
		}
	});
	STK
			.register(
					"core.str.parseURL",
					function(a) {
						return function(a) {
							var b = /^(?:([A-Za-z]+):(\/{0,3}))?([0-9.\-A-Za-z]+\.[0-9A-Za-z]+)?(?::(\d+))?(?:\/([^?#]*))?(?:\?([^#]*))?(?:#(.*))?$/, c = [
									"url", "scheme", "slash", "host", "port",
									"path", "query", "hash" ], d = b.exec(a), e = {};
							for ( var f = 0, g = c.length; f < g; f += 1)
								e[c[f]] = d[f] || "";
							return e
						}
					});
	STK.register("core.json.jsonToQuery", function(a) {
		var b = function(b, c) {
			b = b == null ? "" : b;
			b = a.core.str.trim(b.toString());
			return c ? encodeURIComponent(b) : b
		};
		return function(a, c) {
			var d = [];
			if (typeof a == "object")
				for ( var e in a) {
					if (e === "$nullName") {
						d = d.concat(a[e]);
						continue
					}
					if (a[e] instanceof Array)
						for ( var f = 0, g = a[e].length; f < g; f++)
							d.push(e + "=" + b(a[e][f], c));
					else
						typeof a[e] != "function"
								&& d.push(e + "=" + b(a[e], c))
				}
			return d.length ? d.join("&") : ""
		}
	});
	STK
			.register(
					"core.util.URL",
					function(a) {
						return function(b, c) {
							var d = a.core.obj.parseParam({
								isEncodeQuery : !1,
								isEncodeHash : !1
							}, c || {}), e = {}, f = a.core.str.parseURL(b), g = a.core.json
									.queryToJson(f.query), h = a.core.json
									.queryToJson(f.hash);
							e.setParam = function(a, b) {
								g[a] = b;
								return this
							};
							e.getParam = function(a) {
								return g[a]
							};
							e.setParams = function(a) {
								for ( var b in a)
									e.setParam(b, a[b]);
								return this
							};
							e.setHash = function(a, b) {
								h[a] = b;
								return this
							};
							e.getHash = function(a) {
								return h[a]
							};
							e.valueOf = e.toString = function() {
								var b = [], c = a.core.json.jsonToQuery(g,
										d.isEncodeQuery), e = a.core.json
										.jsonToQuery(h, d.isEncodeQuery);
								if (f.scheme != "") {
									b.push(f.scheme + ":");
									b.push(f.slash)
								}
								if (f.host != "") {
									b.push(f.host);
									if (f.port != "") {
										b.push(":");
										b.push(f.port)
									}
								}
								b.push("/");
								b.push(f.path);
								c != "" && b.push("?" + c);
								e != "" && b.push("#" + e);
								return b.join("")
							};
							return e
						}
					});
	STK.register("core.io.scriptLoader", function(a) {
		var b = {}, c = {
			url : "",
			charset : "UTF-8",
			timeout : 3e4,
			args : {},
			onComplete : a.core.func.empty,
			onTimeout : null,
			isEncode : !1,
			uniqueID : null
		};
		return function(d) {
			var e, f, g = a.core.obj.parseParam(c, d);
			if (g.url == "")
				throw "scriptLoader: url is null";
			var h = g.uniqueID || a.core.util.getUniqueKey();
			e = b[h];
			if (e != null && a.IE != !0) {
				a.core.dom.removeNode(e);
				e = null
			}
			e == null && (e = b[h] = a.C("script"));
			e.charset = g.charset;
			e.id = "scriptRequest_script_" + h;
			e.type = "text/javascript";
			g.onComplete != null
					&& (a.IE ? e.onreadystatechange = function() {
						if (e.readyState.toLowerCase() == "loaded"
								|| e.readyState.toLowerCase() == "complete") {
							try {
								clearTimeout(f);
								document.getElementsByTagName("head")[0]
										.removeChild(e);
								e.onreadystatechange = null
							} catch (a) {
							}
							g.onComplete()
						}
					} : e.onload = function() {
						try {
							clearTimeout(f);
							a.core.dom.removeNode(e)
						} catch (b) {
						}
						g.onComplete()
					});
			e.src = STK.core.util.URL(g.url, {
				isEncodeQuery : g.isEncode
			}).setParams(g.args);
			document.getElementsByTagName("head")[0].appendChild(e);
			g.timeout > 0 && g.onTimeout != null && (f = setTimeout(function() {
				try {
					document.getElementsByTagName("head")[0].removeChild(e)
				} catch (a) {
				}
				g.onTimeout()
			}, g.timeout));
			return e
		}
	});
	STK.register("core.io.jsonp", function(a) {
		return function(b) {
			var c = a.core.obj.parseParam({
				url : "",
				charset : "UTF-8",
				timeout : 3e4,
				args : {},
				onComplete : null,
				onTimeout : null,
				responseName : null,
				isEncode : !1,
				varkey : "callback"
			}, b), d = -1, e = c.responseName || "STK_"
					+ a.core.util.getUniqueKey();
			c.args[c.varkey] = e;
			var f = c.onComplete, g = c.onTimeout;
			window[e] = function(a) {
				if (d != 2 && f != null) {
					d = 1;
					f(a)
				}
			};
			c.onComplete = null;
			c.onTimeout = function() {
				if (d != 1 && g != null) {
					d = 2;
					g()
				}
			};
			return a.core.io.scriptLoader(c)
		}
	});
	STK.register("core.util.listener", function(a) {
		return function() {
			function f() {
				if (d.length != 0) {
					clearTimeout(e);
					var b = d.splice(0, 1)[0];
					try {
						b.func.apply(b.func, [].concat(b.data))
					} catch (c) {
						a.log("[error][listener]: One of " + b + "-" + b
								+ " function execute error.")
					}
					e = setTimeout(f, 25)
				}
			}
			var b = {}, c, d = [], e, g = {
				conn : function() {
					var a = window;
					while (a != top) {
						a = a.parent;
						a.STK && a.STK.core && a.STK.core.util
								&& a.STK.core.util.listener != null && (c = a)
					}
				},
				register : function(a, d, e) {
					if (c != null)
						c.STK.core.util.listener.register(a, d, e);
					else {
						b[a] = b[a] || {};
						b[a][d] = b[a][d] || [];
						b[a][d].push(e)
					}
				},
				fire : function(a, e, g) {
					if (c != null)
						c.listener.fire(a, e, g);
					else {
						var h, i, j;
						if (b[a] && b[a][e] && b[a][e].length > 0) {
							h = b[a][e];
							h.data_cache = g;
							for (i = 0, j = h.length; i < j; i++)
								d.push({
									channel : a,
									evt : e,
									func : h[i],
									data : g
								});
							f()
						}
					}
				},
				remove : function(a, d, e) {
					if (c != null)
						c.STK.core.util.listener.remove(a, d, e);
					else if (b[a] && b[a][d])
						for ( var f = 0, g = b[a][d].length; f < g; f++)
							if (b[a][d][f] === e) {
								b[a][d].splice(f, 1);
								break
							}
				},
				list : function() {
					return b
				},
				cache : function(a, d) {
					if (c != null)
						return c.listener.cache(a, d);
					if (b[a] && b[a][d])
						return b[a][d].data_cache
				}
			};
			return g
		}()
	});
	STK.register("core.util.language", function(a) {
		return function(a, b) {
			return a.replace(/#L\{((.*?)(?:[^\\]))\}/ig, function() {
				var a = arguments[1], c;
				b && b[a] !== undefined ? c = b[a] : c = a;
				return c
			})
		}
	});
	STK
			.register(
					"core.util.easyTemplate",
					function(a) {
						var b = function(a, c) {
							if (!a)
								return "";
							if (a !== b.template) {
								b.template = a;
								b.aStatement = b.parsing(b.separate(a))
							}
							var d = b.aStatement, e = function(a) {
								a && (c = a);
								return arguments.callee
							};
							e.toString = function() {
								return (new Function(d[0], d[1]))(c)
							};
							return e
						};
						b.separate = function(a) {
							var b = /\\'/g, c = a
									.replace(
											/(<(\/?)#(.*?(?:\(.*?\))*)>)|(')|([\r\n\t])|(\$\{([^\}]*?)\})/g,
											function(a, c, d, e, f, g, h, i) {
												if (c)
													return "{|}"
															+ (d ? "-" : "+")
															+ e + "{|}";
												if (f)
													return "\\'";
												if (g)
													return "";
												if (h)
													return "'+("
															+ i.replace(b, "'")
															+ ")+'"
											});
							return c
						};
						b.parsing = function(a) {
							var b, c, d, e, f, g, h, i = [ "var aRet = [];" ];
							h = a.split(/\{\|\}/);
							var j = /\s/;
							while (h.length) {
								d = h.shift();
								if (!d)
									continue;
								f = d.charAt(0);
								if (f !== "+" && f !== "-") {
									d = "'" + d + "'";
									i.push("aRet.push(" + d + ");");
									continue
								}
								e = d.split(j);
								switch (e[0]) {
								case "+et":
									b = e[1];
									c = e[2];
									i.push('aRet.push("<!--' + b
											+ ' start-->");');
									break;
								case "-et":
									i
											.push('aRet.push("<!--' + b
													+ ' end-->");');
									break;
								case "+if":
									e.splice(0, 1);
									i.push("if" + e.join(" ") + "{");
									break;
								case "+elseif":
									e.splice(0, 1);
									i.push("}else if" + e.join(" ") + "{");
									break;
								case "-if":
									i.push("}");
									break;
								case "+else":
									i.push("}else{");
									break;
								case "+list":
									i
											.push("if("
													+ e[1]
													+ ".constructor === Array){with({i:0,l:"
													+ e[1] + ".length," + e[3]
													+ "_index:0," + e[3]
													+ ":null}){for(i=l;i--;){"
													+ e[3] + "_index=(l-i-1);"
													+ e[3] + "=" + e[1] + "["
													+ e[3] + "_index];");
									break;
								case "-list":
									i.push("}}}");
									break;
								default:
								}
							}
							i.push('return aRet.join("");');
							return [ c, i.join("") ]
						};
						return b
					});
	STK.register("common.extra.toplang", function() {
		return {
			"首页" : "首頁",
			"应用" : "應用",
			"浏览热门应用" : "浏覽熱門應用",
			"管理/查看更多应用" : "管理/查看更多應用",
			"查看我的游戏" : "查看我的遊戲",
			"游戏" : "遊戲",
			"帐号" : "帳號",
			"帐号设置" : "帳號設置",
			"版本选择" : "版本選擇",
			"工具设置" : "工具設置",
			"我的微币" : "我的微幣",
			"还没有微博帐号？" : "還沒有微博帳號？",
			"注册" : "注冊",
			"登录" : "登錄",
			"相关微博" : "相關微博",
			"相关用户" : "相關用戶",
			"粉丝" : "粉絲",
			"相关微群" : "相關微群",
			"群号" : "群號",
			"成员" : "成員",
			"相关应用" : "相關應用",
			"你还没有加入任何微群，现在就去" : "你還沒有加入任何微群，現在就去",
			"发现微群" : "發現微群",
			"热门微群推荐" : "熱門微群推薦",
			"条新评论" : "條新評論",
			"查看评论" : "查看評論",
			"位新粉丝" : "位新粉絲",
			"查看粉丝" : "查看粉絲",
			"条新私信" : "條新私信",
			"条新快速评论" : "條新快速評論",
			"查看快速评论" : "查看快速評論",
			"条新密友邀请" : "條新密友邀請",
			"查看密友邀请" : "查看密友邀請",
			"条新共同评论" : "條新共同評論",
			"查看共同评论" : "查看共同評論",
			"条新@提到我" : "條新@提到我",
			"条群内新消息" : "條群內新消息",
			"条相册新消息" : "條相冊新消息",
			"条新通知" : "條新通知",
			"条新邀请" : "條新邀請",
			"查看邀请" : "查看邀請",
			"查看群内消息" : "查看群內消息",
			"查看相册消息" : "查看相冊消息",
			"还没有收到私信/评论/@我，要记得和朋友保持联系哦。" : "還沒有收到私信/評論/@我，要記得和朋友保持聯系哦。",
			"正在加载,请稍候..." : "正在加載,請稍候...",
			"模板设置" : "模板設置",
			"广场" : "廣場",
			"用户" : "用戶",
			"浏览热门游戏" : "瀏覽熱門遊戲",
			"常用游戏" : "常用遊戲",
			"你还没有玩过任何游戏，现在就去" : "你還沒有玩過任何遊戲，現在就去",
			"体验一下" : "體驗一下",
			"风云榜" : "風雲榜",
			"微话题" : "微話題",
			"微博精选" : "微博精選",
			"随便看看" : "隨便看看",
			"微访谈" : "微訪談",
			"查看更多有趣内容" : "查看更多有趣內容",
			"微博达人" : "微博達人",
			"我的微号" : "我的微號",
			"手机" : "手機",
			"我的会员" : "我的會員",
			"会员" : "會員",
			"微群将移到隔壁“应用”里啦~" : "微群將移到隔壁“應用”里啦~",
			"推荐微吧" : "推薦微吧",
			"更多推荐" : "更多推薦",
			"我关注的微吧" : "我關注的微吧",
			"热门微吧" : "熱門微吧",
			"去" : "去",
			"微吧首页" : "微吧首頁",
			"看看，你会发现更多感兴趣的微吧" : "看看，你會發現更多感興趣的微吧",
			"管理微吧" : "管理微吧",
			"管理" : "管理",
			"微群" : "微群",
			"进入微群" : "進入微群",
			"微吧长微博" : "微吧長微博",
			"从这里进入微群" : "從這裡進入微群",
			"搜微博、找人、搜奥运" : "搜微博、找人、搜奧運"
		}
	});
	STK
			.register(
					"core.dom.ready",
					function(a) {
						var b = [], c = !1, d = a.core.func.getType, e = a.core.util.browser, f = a.core.evt.addEvent, g = function() {
							if (!c && document.readyState === "complete")
								return !0;
							return c
						}, h = function() {
							if (c != !0) {
								c = !0;
								for ( var a = 0, e = b.length; a < e; a++)
									if (d(b[a]) === "function")
										try {
											b[a].call()
										} catch (f) {
										}
								b = []
							}
						}, i = function() {
							if (g())
								h();
							else {
								try {
									document.documentElement.doScroll("left")
								} catch (a) {
									setTimeout(arguments.callee, 25);
									return
								}
								h()
							}
						}, j = function() {
							g() ? h() : setTimeout(arguments.callee, 25)
						}, k = function() {
							f(document, "DOMContentLoaded", h)
						}, l = function() {
							f(window, "load", h)
						};
						if (!g()) {
							a.IE && window === window.top && i();
							k();
							j();
							l()
						}
						return function(a) {
							g() ? d(a) === "function" && a.call() : b.push(a)
						}
					});
	STK
			.register(
					"core.util.hideContainer",
					function(a) {
						var b, c = function() {
							if (!b) {
								b = a.C("div");
								b.style.cssText = "position:absolute;top:-9999px;left:-9999px;";
								document.getElementsByTagName("head")[0]
										.appendChild(b)
							}
						}, d = {
							appendChild : function(d) {
								if (a.core.dom.isNode(d)) {
									c();
									b.appendChild(d)
								}
							},
							removeChild : function(c) {
								a.core.dom.isNode(c) && b && b.removeChild(c)
							}
						};
						return d
					});
	STK.register("core.dom.getSize", function(a) {
		var b = function(b) {
			if (!a.core.dom.isNode(b))
				throw "core.dom.getSize need Element as first parameter";
			return {
				width : b.offsetWidth,
				height : b.offsetHeight
			}
		}, c = function(a) {
			var c = null;
			if (a.style.display === "none") {
				a.style.visibility = "hidden";
				a.style.display = "";
				c = b(a);
				a.style.display = "none";
				a.style.visibility = "visible"
			} else
				c = b(a);
			return c
		};
		return function(b) {
			var d = {};
			if (!b.parentNode) {
				a.core.util.hideContainer.appendChild(b);
				d = c(b);
				a.core.util.hideContainer.removeChild(b)
			} else
				d = c(b);
			return d
		}
	});
	STK
			.register(
					"core.util.swf",
					function(a) {
						function b(b, c) {
							var d = a.core.obj.parseParam(
									{
										id : "swf_"
												+ parseInt(Math.random() * 1e4,
														10),
										width : 1,
										height : 1,
										attrs : {},
										paras : {},
										flashvars : {},
										html : ""
									}, c);
							if (b == null)
								throw "swf: [sURL] 未定义";
							var e, f = [], g = [];
							for (e in d.attrs)
								g.push(e + '="' + d.attrs[e] + '" ');
							var h = [];
							for (e in d.flashvars)
								h.push(e + "=" + d.flashvars[e]);
							d.paras.flashvars = h.join("&");
							if (a.IE) {
								f
										.push('<object width="'
												+ d.width
												+ '" height="'
												+ d.height
												+ '" id="'
												+ d.id
												+ '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ');
								f.push(g.join(""));
								f.push('><param name="movie" value="' + b
										+ '" />');
								for (e in d.paras)
									f.push('<param name="' + e + '" value="'
											+ d.paras[e] + '" />');
								f.push("</object>")
							} else {
								f
										.push('<embed width="'
												+ d.width
												+ '" height="'
												+ d.height
												+ '" id="'
												+ d.id
												+ '" src="'
												+ b
												+ '" type="application/x-shockwave-flash" ');
								f.push(g.join(""));
								for (e in d.paras)
									f.push(e + '="' + d.paras[e] + '" ');
								f.push(" />")
							}
							d.html = f.join("");
							return d
						}
						var c = {};
						c.create = function(c, d, e) {
							var f = a.E(c);
							if (f == null)
								throw "swf: [" + c + "] 未找到";
							var g = b(d, e);
							f.innerHTML = g.html;
							return a.E(g.id)
						};
						c.html = function(a, c) {
							var d = b(a, c);
							return d.html
						};
						c.check = function() {
							var b = -1;
							if (a.IE)
								try {
									var c = new ActiveXObject(
											"ShockwaveFlash.ShockwaveFlash");
									b = c.GetVariable("$version")
								} catch (d) {
								}
							else
								navigator.plugins["Shockwave Flash"]
										&& (b = navigator.plugins["Shockwave Flash"].description);
							return b
						};
						return c
					});
	STK.register("common.listener", function(a) {
		var b = {}, c = {};
		c.define = function(c, d) {
			if (b[c] != null)
				throw "common.listener.define: 频道已被占用";
			b[c] = d;
			var e = {};
			e.register = function(d, e) {
				if (b[c] == null)
					throw "common.listener.define: 频道未定义";
				a.listener.register(c, d, e)
			};
			e.fire = function(d, e) {
				if (b[c] == null)
					throw "commonlistener.define: 频道未定义";
				a.listener.fire(c, d, e)
			};
			e.remove = function(b, d) {
				a.listener.remove(c, b, d)
			};
			e.cache = function(b) {
				return a.listener.cache(c, b)
			};
			return e
		};
		return c
	});
	STK.register("common.channel.topTip", function(a) {
		var b = [ "refresh", "readed" ];
		return a.common.listener.define("common.channel.topTip", b)
	});
	STK.register("kit.extra.merge", function(a) {
		return function(a, b) {
			var c = {};
			for ( var d in a)
				c[d] = a[d];
			for ( var d in b)
				c[d] = b[d];
			return c
		}
	});
	STK.register("kit.io.ajax", function(a) {
		var b = function(b, c, d) {
			c = c | 0 || 1;
			d = d || "fail";
			var e = b.args;
			e.__rnd && delete e.__rnd;
			(new Image).src = "http://weibolog.sinaapp.com/?t=" + c + "&u="
					+ encodeURIComponent(b.url) + "&p="
					+ encodeURIComponent(a.core.json.jsonToQuery(e)) + "&m="
					+ d
		};
		return function(c) {
			var d, e, f, g, h, i, j;
			i = function(a) {
				h = !1;
				c.onComplete(a, d.args);
				setTimeout(k, 0)
			};
			j = function(a) {
				h = !1;
				typeof c.onFail == "function" && c.onFail(a, d.args);
				setTimeout(k, 0);
				try {
					b(d)
				} catch (e) {
				}
			};
			f = [];
			g = null;
			h = !1;
			d = a.parseParam({
				url : "",
				method : "get",
				responseType : "json",
				timeout : 3e4,
				onTraning : a.funcEmpty,
				isEncode : !0
			}, c);
			d.onComplete = i;
			d.onFail = j;
			d.onTimeout = function() {
				try {
					b(d)
				} catch (a) {
				}
			};
			var k = function() {
				if (!!f.length) {
					if (h === !0)
						return;
					h = !0;
					d.args = f.shift();
					if (d.method.toLowerCase() == "post") {
						var b = a.core.util.URL(d.url);
						b.setParam("__rnd", (new Date).valueOf());
						d.url = b.toString()
					}
					g = a.ajax(d)
				}
			}, l = function(a) {
				while (f.length)
					f.shift();
				h = !1;
				if (g)
					try {
						g.abort()
					} catch (b) {
					}
				g = null
			};
			e = {};
			e.request = function(a) {
				a || (a = {});
				c.noQueue && l();
				if (!c.uniqueRequest || !g) {
					f.push(a);
					a._t = 0;
					k()
				}
			};
			e.abort = l;
			return e
		}
	});
	STK.register("kit.io.jsonp", function(a) {
		return function(b) {
			var c, d, e, f, g;
			c = a.parseParam({
				url : "",
				method : "get",
				responseType : "json",
				varkey : "_v",
				timeout : 3e4,
				onComplete : a.funcEmpty,
				onTraning : a.funcEmpty,
				onFail : a.funcEmpty,
				isEncode : !0
			}, b);
			e = [];
			f = {};
			g = !1;
			var h = function() {
				if (!!e.length) {
					if (g === !0)
						return;
					g = !0;
					f.args = e.shift();
					f.onComplete = function(a) {
						g = !1;
						c.onComplete(a, f.args);
						setTimeout(h, 0)
					};
					f.onFail = function(a) {
						g = !1;
						c.onFail(a);
						setTimeout(h, 0)
					};
					a.jsonp(a.kit.extra.merge(c, {
						args : f.args,
						onComplete : function(a) {
							f.onComplete(a)
						},
						onFail : function(a) {
							try {
								f.onFail(a)
							} catch (b) {
							}
						}
					}))
				}
			};
			d = {};
			d.request = function(a) {
				a || (a = {});
				e.push(a);
				a._t = 1;
				h()
			};
			d.abort = function(a) {
				while (e.length)
					e.shift();
				g = !1;
				f = null
			};
			return d
		}
	});
	STK.register("kit.io.inter", function(a) {
		return function() {
			var b, c, d;
			b = {};
			c = {};
			d = {};
			b.register = function(a, b) {
				if (c[a] !== undefined)
					throw a + " interface has been registered";
				c[a] = b;
				d[a] = {}
			};
			b.hookComplete = function(b, c) {
				var e = a.core.util.getUniqueKey();
				d[b][e] = c;
				return e
			};
			b.removeHook = function(a, b) {
				d[a] && d[a][b] && delete d[a][b]
			};
			b.getTrans = function(b, e) {
				var f = a.kit.extra.merge(c[b], e);
				f.onComplete = function(a, c) {
					try {
						e.onComplete(a, c)
					} catch (f) {
					}
					if (a.code === "100000")
						try {
							e.onSuccess(a, c)
						} catch (f) {
						}
					else
						try {
							if (a.code === "100002") {
								window.location.href = a.data;
								return
							}
							e.onError(a, c)
						} catch (f) {
						}
					for ( var g in d[b])
						try {
							d[b][g](a, c)
						} catch (f) {
						}
				};
				return c[b].requestMode === "jsonp" ? a.kit.io.jsonp(f)
						: c[b].requestMode === "ijax" ? a.kit.io.ijax(f)
								: a.kit.io.ajax(f)
			};
			b.request = function(b, e, f) {
				var g = a.core.json.merge(c[b], e);
				g.onComplete = function(a, c) {
					try {
						e.onComplete(a, f)
					} catch (g) {
					}
					if (a.code === "100000")
						try {
							e.onSuccess(a, f)
						} catch (g) {
						}
					else
						try {
							if (a.code === "100002") {
								window.location.href = a.data;
								return
							}
							e.onError(a, f)
						} catch (g) {
						}
					for ( var h in d[b])
						try {
							d[b][h](a, f)
						} catch (g) {
						}
				};
				g = a.core.obj.cut(g, [ "noqueue" ]);
				g.args = f;
				return c[b].requestMode === "jsonp" ? a.jsonp(g)
						: c[b].requestMode === "ijax" ? a.ijax(g) : a.ajax(g)
			};
			return b
		}
	});

	STK.register("kit.dom.parseDOM", function(a) {
		return function(a) {
			for ( var b in a)
				a[b] && a[b].length == 1 && (a[b] = a[b][0]);
			return a
		}
	});
	STK.register("kit.dom.hover", function(a) {
		return function(b) {
			var c = b.delay || 100, d = b.isover || !1, e = b.act, f = b.extra
					|| [], g = null, h = function(a) {
				d && b.onmouseover.apply(e, [ a ])
			}, i = function(a) {
				d || b.onmouseout.apply(e, [ a ])
			}, j = function(a) {
				d = !0;
				g && clearTimeout(g);
				g = setTimeout(function() {
					h(a)
				}, c)
			}, k = function(a) {
				d = !1;
				g && clearTimeout(g);
				g = setTimeout(function() {
					i(a)
				}, c)
			};
			a.core.evt.addEvent(e, "mouseover", j);
			a.core.evt.addEvent(e, "mouseout", k);
			for ( var l = 0, m = f.length; l < m; l += 1) {
				a.core.evt.addEvent(f[l], "mouseover", j);
				a.core.evt.addEvent(f[l], "mouseout", k)
			}
			var n = {};
			n.destroy = function() {
				a.core.evt.removeEvent(e, "mouseover", j);
				a.core.evt.removeEvent(e, "mouseout", k);
				for ( var b = 0, c = f.length; b < c; b += 1) {
					a.core.evt.removeEvent(f[b], "mouseover", j);
					a.core.evt.removeEvent(f[b], "mouseout", k)
				}
			};
			return n
		}
	});

}();
