'use strict';
!function(module, number) {
  if ("object" == typeof exports && "object" == typeof module) {
    module.exports = number(number.toString()).default;
  } else {
    if ("object" == typeof exports) {
      exports.Quagga = number(number.toString()).default;
    } else {
      module.Quagga = number(number.toString()).default;
    }
  }
}(this, function(value) {
  return function(modules) {
    /**
     * @param {string} moduleId
     * @return {?}
     */
    function __webpack_require__(moduleId) {
      if (installedModules[moduleId]) {
        return installedModules[moduleId].exports;
      }
      var module = installedModules[moduleId] = {
        i : moduleId,
        l : false,
        exports : {}
      };
      return modules[moduleId].call(module.exports, module, module.exports, __webpack_require__), module.l = true, module.exports;
    }
    var installedModules = {};
    return __webpack_require__.m = modules, __webpack_require__.c = installedModules, __webpack_require__.i = function(value) {
      return value;
    }, __webpack_require__.d = function(exports, name, n) {
      if (!__webpack_require__.o(exports, name)) {
        Object.defineProperty(exports, name, {
          configurable : false,
          enumerable : true,
          get : n
        });
      }
    }, __webpack_require__.n = function(module) {
      /** @type {function(): ?} */
      var n = module && module.__esModule ? function() {
        return module.default;
      } : function() {
        return module;
      };
      return __webpack_require__.d(n, "a", n), n;
    }, __webpack_require__.o = function(value, name) {
      return Object.prototype.hasOwnProperty.call(value, name);
    }, __webpack_require__.p = "/", __webpack_require__(__webpack_require__.s = 166);
  }([function(module, canCreateDiscussions) {
    /**
     * @param {?} o
     * @return {?}
     */
    function n(o) {
      /** @type {string} */
      var tp = typeof o;
      return null != o && ("object" == tp || "function" == tp);
    }
    /** @type {function(?): ?} */
    module.exports = n;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @param {!Object} config
     * @param {!Object} supplements
     * @return {?}
     */
    function BarcodeReader(config, supplements) {
      return this._row = [], this.config = config || {}, this.supplements = supplements, this;
    }
    var o = keyGen(3);
    /**
     * @param {!Array} line
     * @param {number} start
     * @return {?}
     */
    BarcodeReader.prototype._nextUnset = function(line, start) {
      var i;
      if (void 0 === start) {
        /** @type {number} */
        start = 0;
      }
      /** @type {number} */
      i = start;
      for (; i < line.length; i++) {
        if (!line[i]) {
          return i;
        }
      }
      return line.length;
    };
    /**
     * @param {!Array} counter
     * @param {!Object} code
     * @param {number} maxSingleError
     * @return {?}
     */
    BarcodeReader.prototype._matchPattern = function(counter, code, maxSingleError) {
      var i;
      var barWidth;
      var max;
      var min;
      /** @type {number} */
      var cumTotal = 0;
      /** @type {number} */
      var value = 0;
      /** @type {number} */
      var sum = 0;
      /** @type {number} */
      var modulo = 0;
      maxSingleError = maxSingleError || this.SINGLE_CODE_ERROR || 1;
      /** @type {number} */
      i = 0;
      for (; i < counter.length; i++) {
        sum = sum + counter[i];
        modulo = modulo + code[i];
      }
      if (sum < modulo) {
        return Number.MAX_VALUE;
      }
      /** @type {number} */
      barWidth = sum / modulo;
      /** @type {number} */
      maxSingleError = maxSingleError * barWidth;
      /** @type {number} */
      i = 0;
      for (; i < counter.length; i++) {
        if (max = counter[i], min = code[i] * barWidth, (value = Math.abs(max - min) / min) > maxSingleError) {
          return Number.MAX_VALUE;
        }
        /** @type {number} */
        cumTotal = cumTotal + value;
      }
      return cumTotal / modulo;
    };
    /**
     * @param {!Array} line
     * @param {number} offset
     * @return {?}
     */
    BarcodeReader.prototype._nextSet = function(line, offset) {
      var i;
      offset = offset || 0;
      /** @type {number} */
      i = offset;
      for (; i < line.length; i++) {
        if (line[i]) {
          return i;
        }
      }
      return line.length;
    };
    /**
     * @param {!Array} counter
     * @param {number} correction
     * @param {!Array} indices
     * @return {undefined}
     */
    BarcodeReader.prototype._correctBars = function(counter, correction, indices) {
      var i = indices.length;
      /** @type {number} */
      var max = 0;
      for (; i--;) {
        if ((max = counter[indices[i]] * (1 - (1 - correction) / 2)) > 1) {
          /** @type {number} */
          counter[indices[i]] = max;
        }
      }
    };
    /**
     * @param {!Object} cmpCounter
     * @param {number} epsilon
     * @return {?}
     */
    BarcodeReader.prototype._matchTrace = function(cmpCounter, epsilon) {
      var i;
      var error;
      /** @type {!Array} */
      var counter = [];
      var self = this;
      var offset = self._nextSet(self._row);
      /** @type {boolean} */
      var isWhite = !self._row[offset];
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0
      };
      if (cmpCounter) {
        /** @type {number} */
        i = 0;
        for (; i < cmpCounter.length; i++) {
          counter.push(0);
        }
        i = offset;
        for (; i < self._row.length; i++) {
          if (self._row[i] ^ isWhite) {
            counter[name]++;
          } else {
            if (name === counter.length - 1) {
              return error = self._matchPattern(counter, cmpCounter), error < epsilon ? (bestMatch.start = i - offset, bestMatch.end = i, bestMatch.counter = counter, bestMatch) : null;
            }
            name++;
            /** @type {number} */
            counter[name] = 1;
            /** @type {boolean} */
            isWhite = !isWhite;
          }
        }
      } else {
        counter.push(0);
        i = offset;
        for (; i < self._row.length; i++) {
          if (self._row[i] ^ isWhite) {
            counter[name]++;
          } else {
            name++;
            counter.push(0);
            /** @type {number} */
            counter[name] = 1;
            /** @type {boolean} */
            isWhite = !isWhite;
          }
        }
      }
      return bestMatch.start = offset, bestMatch.end = self._row.length - 1, bestMatch.counter = counter, bestMatch;
    };
    /**
     * @param {!Object} pattern
     * @return {?}
     */
    BarcodeReader.prototype.decodePattern = function(pattern) {
      var result;
      var self = this;
      return self._row = pattern, result = self._decode(), null === result ? (self._row.reverse(), (result = self._decode()) && (result.direction = BarcodeReader.DIRECTION.REVERSE, result.start = self._row.length - result.start, result.end = self._row.length - result.end)) : result.direction = BarcodeReader.DIRECTION.FORWARD, result && (result.format = self.FORMAT), result;
    };
    /**
     * @param {number} start
     * @param {number} end
     * @param {number} value
     * @return {?}
     */
    BarcodeReader.prototype._matchRange = function(start, end, value) {
      var i;
      start = start < 0 ? 0 : start;
      /** @type {number} */
      i = start;
      for (; i < end; i++) {
        if (this._row[i] !== value) {
          return false;
        }
      }
      return true;
    };
    /**
     * @param {number} offset
     * @param {number} end
     * @param {number} isWhite
     * @return {?}
     */
    BarcodeReader.prototype._fillCounters = function(offset, end, isWhite) {
      var i;
      var self = this;
      /** @type {number} */
      var cntr_map = 0;
      /** @type {!Array} */
      var counters = [];
      isWhite = void 0 === isWhite || isWhite;
      offset = void 0 !== offset ? offset : self._nextUnset(self._row);
      end = end || self._row.length;
      /** @type {number} */
      counters[cntr_map] = 0;
      /** @type {number} */
      i = offset;
      for (; i < end; i++) {
        if (self._row[i] ^ isWhite) {
          counters[cntr_map]++;
        } else {
          cntr_map++;
          /** @type {number} */
          counters[cntr_map] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return counters;
    };
    /**
     * @param {number} start
     * @param {!Array} counter
     * @return {?}
     */
    BarcodeReader.prototype._toCounters = function(start, counter) {
      var i;
      var element = this;
      var rowLength = counter.length;
      var patchLen = element._row.length;
      /** @type {boolean} */
      var isWhite = !element._row[start];
      /** @type {number} */
      var rowCounter = 0;
      o.a.init(counter, 0);
      /** @type {number} */
      i = start;
      for (; i < patchLen; i++) {
        if (element._row[i] ^ isWhite) {
          counter[rowCounter]++;
        } else {
          if (++rowCounter === rowLength) {
            break;
          }
          /** @type {number} */
          counter[rowCounter] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return counter;
    };
    Object.defineProperty(BarcodeReader.prototype, "FORMAT", {
      value : "unknown",
      writeable : false
    });
    BarcodeReader.DIRECTION = {
      FORWARD : 1,
      REVERSE : -1
    };
    BarcodeReader.Exception = {
      StartNotFoundException : "Start-Info was not found!",
      CodeNotFoundException : "Code could not be found!",
      PatternNotFoundException : "Pattern could not be found!"
    };
    BarcodeReader.CONFIG_KEYS = {};
    /** @type {function(!Object, !Object): ?} */
    defaultTagAttributes.a = BarcodeReader;
  }, function(module, canCreateDiscussions) {
    /** @type {function(*): boolean} */
    var isArray = Array.isArray;
    /** @type {function(*): boolean} */
    module.exports = isArray;
  }, function(canCreateDiscussions, ts, n) {
    ts.a = {
      init : function(value, i) {
        var n = value.length;
        for (; n--;) {
          /** @type {!Function} */
          value[n] = i;
        }
      },
      shuffle : function(array) {
        var min;
        var tmp;
        /** @type {number} */
        var i = array.length - 1;
        i;
        for (; i >= 0; i--) {
          /** @type {number} */
          min = Math.floor(Math.random() * i);
          tmp = array[i];
          array[i] = array[min];
          array[min] = tmp;
        }
        return array;
      },
      toPointList : function(arr) {
        var j;
        var i;
        /** @type {!Array} */
        var valueCopy = [];
        /** @type {!Array} */
        var o = [];
        /** @type {number} */
        j = 0;
        for (; j < arr.length; j++) {
          /** @type {!Array} */
          valueCopy = [];
          /** @type {number} */
          i = 0;
          for (; i < arr[j].length; i++) {
            valueCopy[i] = arr[j][i];
          }
          /** @type {string} */
          o[j] = "[" + valueCopy.join(",") + "]";
        }
        return "[" + o.join(",\r\n") + "]";
      },
      threshold : function(data, e, ctx) {
        var i;
        /** @type {!Array} */
        var pixels = [];
        /** @type {number} */
        i = 0;
        for (; i < data.length; i++) {
          if (ctx.apply(data, [data[i]]) >= e) {
            pixels.push(data[i]);
          }
        }
        return pixels;
      },
      maxIndex : function(arr) {
        var i;
        /** @type {number} */
        var max = 0;
        /** @type {number} */
        i = 0;
        for (; i < arr.length; i++) {
          if (arr[i] > arr[max]) {
            /** @type {number} */
            max = i;
          }
        }
        return max;
      },
      max : function findMaxFret(scores) {
        var i;
        /** @type {number} */
        var max = 0;
        /** @type {number} */
        i = 0;
        for (; i < scores.length; i++) {
          if (scores[i] > max) {
            max = scores[i];
          }
        }
        return max;
      },
      sum : function reparser(arr) {
        var i = arr.length;
        /** @type {number} */
        var source = 0;
        for (; i--;) {
          source = source + arr[i];
        }
        return source;
      }
    };
  }, function(canCreateDiscussions, defaultTagAttributes, fn) {
    /**
     * @param {?} opts
     * @param {?} rwd
     * @return {undefined}
     */
    function I2of5Reader(opts, rwd) {
      opts = _req()(getDefaulConfig(), opts);
      o.a.call(this, opts, rwd);
    }
    /**
     * @return {?}
     */
    function getDefaulConfig() {
      var widgetData = {};
      return Object.keys(I2of5Reader.CONFIG_KEYS).forEach(function(key) {
        widgetData[key] = I2of5Reader.CONFIG_KEYS[key].default;
      }), widgetData;
    }
    var req = fn(28);
    var _req = fn.n(req);
    var o = fn(1);
    /** @type {function(!Object, ...(Object|null)): !Object} */
    var callback = Object.assign || function(b) {
      /** @type {number} */
      var i = 1;
      for (; i < arguments.length; i++) {
        var data = arguments[i];
        var k;
        for (k in data) {
          if (Object.prototype.hasOwnProperty.call(data, k)) {
            b[k] = data[k];
          }
        }
      }
      return b;
    };
    var properties = {
      CODE_L_START : {
        value : 0
      },
      CODE_G_START : {
        value : 10
      },
      START_PATTERN : {
        value : [1, 1, 1]
      },
      STOP_PATTERN : {
        value : [1, 1, 1]
      },
      MIDDLE_PATTERN : {
        value : [1, 1, 1, 1, 1]
      },
      EXTENSION_START_PATTERN : {
        value : [1, 1, 2]
      },
      CODE_PATTERN : {
        value : [[3, 2, 1, 1], [2, 2, 2, 1], [2, 1, 2, 2], [1, 4, 1, 1], [1, 1, 3, 2], [1, 2, 3, 1], [1, 1, 1, 4], [1, 3, 1, 2], [1, 2, 1, 3], [3, 1, 1, 2], [1, 1, 2, 3], [1, 2, 2, 2], [2, 2, 1, 2], [1, 1, 4, 1], [2, 3, 1, 1], [1, 3, 2, 1], [4, 1, 1, 1], [2, 1, 3, 1], [3, 1, 2, 1], [2, 1, 1, 3]]
      },
      CODE_FREQUENCY : {
        value : [0, 11, 13, 14, 19, 25, 28, 21, 22, 26]
      },
      SINGLE_CODE_ERROR : {
        value : .7
      },
      AVG_CODE_ERROR : {
        value : .48
      },
      FORMAT : {
        value : "ean_13",
        writeable : false
      }
    };
    /** @type {!Object} */
    I2of5Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?, ?): undefined} */
    I2of5Reader.prototype.constructor = I2of5Reader;
    /**
     * @param {number} start
     * @param {number} correction
     * @return {?}
     */
    I2of5Reader.prototype._decodeCode = function(start, correction) {
      var i;
      var code;
      var error;
      /** @type {!Array} */
      var counter = [0, 0, 0, 0];
      var self = this;
      /** @type {number} */
      var offset = start;
      /** @type {boolean} */
      var isWhite = !self._row[offset];
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : start,
        end : start
      };
      if (!correction) {
        correction = self.CODE_PATTERN.length;
      }
      i = offset;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            /** @type {number} */
            code = 0;
            for (; code < correction; code++) {
              if ((error = self._matchPattern(counter, self.CODE_PATTERN[code])) < bestMatch.error) {
                /** @type {number} */
                bestMatch.code = code;
                bestMatch.error = error;
              }
            }
            return bestMatch.end = i, bestMatch.error > self.AVG_CODE_ERROR ? null : bestMatch;
          }
          name++;
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @param {!Object} pattern
     * @param {number} offset
     * @param {number} isWhite
     * @param {number} tryHarder
     * @param {!Array} shouldLog
     * @return {?}
     */
    I2of5Reader.prototype._findPattern = function(pattern, offset, isWhite, tryHarder, shouldLog) {
      var i;
      var error;
      var j;
      var sum;
      /** @type {!Array} */
      var counter = [];
      var self = this;
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0
      };
      if (!offset) {
        offset = self._nextSet(self._row);
      }
      if (void 0 === isWhite) {
        /** @type {boolean} */
        isWhite = false;
      }
      if (void 0 === tryHarder) {
        /** @type {boolean} */
        tryHarder = true;
      }
      if (void 0 === shouldLog) {
        shouldLog = self.AVG_CODE_ERROR;
      }
      /** @type {number} */
      i = 0;
      for (; i < pattern.length; i++) {
        /** @type {number} */
        counter[i] = 0;
      }
      /** @type {number} */
      i = offset;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            /** @type {number} */
            sum = 0;
            /** @type {number} */
            j = 0;
            for (; j < counter.length; j++) {
              sum = sum + counter[j];
            }
            if ((error = self._matchPattern(counter, pattern)) < shouldLog) {
              return bestMatch.error = error, bestMatch.start = i - sum, bestMatch.end = i, bestMatch;
            }
            if (!tryHarder) {
              return null;
            }
            /** @type {number} */
            j = 0;
            for (; j < counter.length - 2; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[counter.length - 2] = 0;
            /** @type {number} */
            counter[counter.length - 1] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._findStart = function() {
      var leadingWhitespaceStart;
      var startInfo;
      var self = this;
      var offset = self._nextSet(self._row);
      for (; !startInfo;) {
        if (!(startInfo = self._findPattern(self.START_PATTERN, offset))) {
          return null;
        }
        if ((leadingWhitespaceStart = startInfo.start - (startInfo.end - startInfo.start)) >= 0 && self._matchRange(leadingWhitespaceStart, startInfo.start, 0)) {
          return startInfo;
        }
        offset = startInfo.end;
        /** @type {null} */
        startInfo = null;
      }
    };
    /**
     * @param {!Object} endInfo
     * @return {?}
     */
    I2of5Reader.prototype._verifyTrailingWhitespace = function(endInfo) {
      var trailingWhitespaceEnd;
      var self = this;
      return trailingWhitespaceEnd = endInfo.end + (endInfo.end - endInfo.start), trailingWhitespaceEnd < self._row.length && self._matchRange(endInfo.end, trailingWhitespaceEnd, 0) ? endInfo : null;
    };
    /**
     * @param {undefined} offset
     * @param {boolean} isWhite
     * @return {?}
     */
    I2of5Reader.prototype._findEnd = function(offset, isWhite) {
      var self = this;
      var lastStart = self._findPattern(self.STOP_PATTERN, offset, isWhite, false);
      return null !== lastStart ? self._verifyTrailingWhitespace(lastStart) : null;
    };
    /**
     * @param {number} name
     * @return {?}
     */
    I2of5Reader.prototype._calculateFirstDigit = function(name) {
      var i;
      var rpbListKeysResp = this;
      /** @type {number} */
      i = 0;
      for (; i < rpbListKeysResp.CODE_FREQUENCY.length; i++) {
        if (name === rpbListKeysResp.CODE_FREQUENCY[i]) {
          return i;
        }
      }
      return null;
    };
    /**
     * @param {!Object} code
     * @param {!Array} result
     * @param {!Array} decodedCodes
     * @return {?}
     */
    I2of5Reader.prototype._decodePayload = function(code, result, decodedCodes) {
      var r;
      var index;
      var self = this;
      /** @type {number} */
      var int = 0;
      /** @type {number} */
      r = 0;
      for (; r < 6; r++) {
        if (!(code = self._decodeCode(code.end))) {
          return null;
        }
        if (code.code >= self.CODE_G_START) {
          /** @type {number} */
          code.code = code.code - self.CODE_G_START;
          /** @type {number} */
          int = int | 1 << 5 - r;
        } else {
          /** @type {number} */
          int = int | 0 << 5 - r;
        }
        result.push(code.code);
        decodedCodes.push(code);
      }
      if (null === (index = self._calculateFirstDigit(int))) {
        return null;
      }
      if (result.unshift(index), null === (code = self._findPattern(self.MIDDLE_PATTERN, code.end, true, false))) {
        return null;
      }
      decodedCodes.push(code);
      /** @type {number} */
      r = 0;
      for (; r < 6; r++) {
        if (!(code = self._decodeCode(code.end, self.CODE_G_START))) {
          return null;
        }
        decodedCodes.push(code);
        result.push(code.code);
      }
      return code;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._decode = function() {
      var startInfo;
      var code;
      var self = this;
      /** @type {!Array} */
      var result = [];
      /** @type {!Array} */
      var decodedCodes = [];
      var section = {};
      if (!(startInfo = self._findStart())) {
        return null;
      }
      if (code = {
        code : startInfo.code,
        start : startInfo.start,
        end : startInfo.end
      }, decodedCodes.push(code), !(code = self._decodePayload(code, result, decodedCodes))) {
        return null;
      }
      if (!(code = self._findEnd(code.end, false))) {
        return null;
      }
      if (decodedCodes.push(code), !self._checksum(result)) {
        return null;
      }
      if (this.supplements.length > 0) {
        var data = this._decodeExtensions(code.end);
        if (!data) {
          return null;
        }
        var taskTime = data.decodedCodes[data.decodedCodes.length - 1];
        var lastStart = {
          start : taskTime.start + ((taskTime.end - taskTime.start) / 2 | 0),
          end : taskTime.end
        };
        if (!self._verifyTrailingWhitespace(lastStart)) {
          return null;
        }
        section = {
          supplement : data,
          code : result.join("") + data.code
        };
      }
      return callback({
        code : result.join(""),
        start : startInfo.start,
        end : code.end,
        codeset : "",
        startInfo : startInfo,
        decodedCodes : decodedCodes
      }, section);
    };
    /**
     * @param {undefined} startNode
     * @return {?}
     */
    I2of5Reader.prototype._decodeExtensions = function(startNode) {
      var i;
      var s;
      var start = this._nextSet(this._row, startNode);
      var stats = this._findPattern(this.EXTENSION_START_PATTERN, start, false, false);
      if (null === stats) {
        return null;
      }
      /** @type {number} */
      i = 0;
      for (; i < this.supplements.length; i++) {
        if (null !== (s = this.supplements[i].decode(this._row, stats.end))) {
          return {
            code : s.code,
            start : start,
            startInfo : stats,
            end : s.end,
            codeset : "",
            decodedCodes : s.decodedCodes
          };
        }
      }
      return null;
    };
    /**
     * @param {!Array} result
     * @return {?}
     */
    I2of5Reader.prototype._checksum = function(result) {
      var i;
      /** @type {number} */
      var txt = 0;
      /** @type {number} */
      i = result.length - 2;
      for (; i >= 0; i = i - 2) {
        txt = txt + result[i];
      }
      /** @type {number} */
      txt = txt * 3;
      /** @type {number} */
      i = result.length - 1;
      for (; i >= 0; i = i - 2) {
        txt = txt + result[i];
      }
      return txt % 10 == 0;
    };
    I2of5Reader.CONFIG_KEYS = {
      supplements : {
        type : "arrayOf(string)",
        default : [],
        description : "Allowed extensions to be decoded (2 and/or 5)"
      }
    };
    /** @type {function(?, ?): undefined} */
    defaultTagAttributes.a = I2of5Reader;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    var freeGlobal = __webpack_require__(38);
    /** @type {(Window|boolean)} */
    var freeSelf = "object" == typeof self && self && self.Object === Object && self;
    var storeMixin = freeGlobal || freeSelf || Function("return this")();
    module.exports = storeMixin;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} o
     * @return {?}
     */
    function n(o) {
      return null != o && "object" == typeof o;
    }
    /** @type {function(?): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Object} b
     * @return {?}
     */
    function BinaryBundle(b) {
      /** @type {!Float32Array} */
      var a = new Float32Array(2);
      return a[0] = b[0], a[1] = b[1], a;
    }
    /** @type {function(!Object): ?} */
    module.exports = BinaryBundle;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} value
     * @return {?}
     */
    function baseGetTag(value) {
      return null == value ? void 0 === value ? index : v2 : symToStringTag && symToStringTag in Object(value) ? getRawTag(value) : objectToString(value);
    }
    var Symbol = __webpack_require__(11);
    var getRawTag = __webpack_require__(119);
    var objectToString = __webpack_require__(146);
    /** @type {string} */
    var v2 = "[object Null]";
    /** @type {string} */
    var index = "[object Undefined]";
    var symToStringTag = Symbol ? Symbol.toStringTag : void 0;
    /** @type {function(?): ?} */
    module.exports = baseGetTag;
  }, function(canCreateDiscussions, scope, n) {
    scope.a = {
      drawRect : function(bounds, size, ctx, options) {
        ctx.strokeStyle = options.color;
        ctx.fillStyle = options.color;
        /** @type {number} */
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.strokeRect(bounds.x, bounds.y, size.x, size.y);
      },
      drawPath : function(data, item, ctx, options) {
        ctx.strokeStyle = options.color;
        ctx.fillStyle = options.color;
        ctx.lineWidth = options.lineWidth;
        ctx.beginPath();
        ctx.moveTo(data[0][item.x], data[0][item.y]);
        /** @type {number} */
        var i = 1;
        for (; i < data.length; i++) {
          ctx.lineTo(data[i][item.x], data[i][item.y]);
        }
        ctx.closePath();
        ctx.stroke();
      },
      drawImage : function(c, cell, ctx) {
        var obj;
        var data = ctx.getImageData(0, 0, cell.x, cell.y);
        var m = data.data;
        var t = c.length;
        var u = m.length;
        if (u / t != 4) {
          return false;
        }
        for (; t--;) {
          obj = c[t];
          /** @type {number} */
          m[--u] = 255;
          m[--u] = obj;
          m[--u] = obj;
          m[--u] = obj;
        }
        return ctx.putImageData(data, 0, 0), true;
      }
    };
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {string} o
     * @return {undefined}
     */
    function self(o) {
      /** @type {number} */
      var j = -1;
      var r_len = null == o ? 0 : o.length;
      this.clear();
      for (; ++j < r_len;) {
        var a = o[j];
        this.set(a[0], a[1]);
      }
    }
    var listCacheClear = __webpack_require__(133);
    var method = __webpack_require__(134);
    var hashGet = __webpack_require__(135);
    var has = __webpack_require__(136);
    var cookie = __webpack_require__(137);
    self.prototype.clear = listCacheClear;
    self.prototype.delete = method;
    self.prototype.get = hashGet;
    self.prototype.has = has;
    self.prototype.set = cookie;
    /** @type {function(string): undefined} */
    module.exports = self;
  }, function(module, canCreateDiscussions, interpret) {
    var root = interpret(5);
    var Symbol = root.Symbol;
    module.exports = Symbol;
  }, function(task, canCreateDiscussions, pointFromEvent) {
    /**
     * @param {!Array} a
     * @param {number} n
     * @return {?}
     */
    function r(a, n) {
      var r = a.length;
      for (; r--;) {
        if (p(a[r][0], n)) {
          return r;
        }
      }
      return -1;
    }
    var p = pointFromEvent(17);
    /** @type {function(!Array, number): ?} */
    task.exports = r;
  }, function(task, canCreateDiscussions, n) {
    /**
     * @param {(Array|!Function|string)} c
     * @param {number} n
     * @return {?}
     */
    function r(c, n) {
      return m(c) ? c : p(c, n) ? [c] : a(f(c));
    }
    var m = n(2);
    var p = n(130);
    var a = n(154);
    var f = n(165);
    /** @type {function((Array|!Function|string), number): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!Object} value
     * @param {number} key
     * @return {?}
     */
    function getMapData(value, key) {
      var data = value.__data__;
      return isKeyable(key) ? data["string" == typeof key ? "string" : "hash"] : data.map;
    }
    var isKeyable = __webpack_require__(131);
    /** @type {function(!Object, number): ?} */
    module.exports = getMapData;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {number} c
     * @param {number} a
     * @return {?}
     */
    function n(c, a) {
      return !!(a = null == a ? previous : a) && ("number" == typeof c || matchLetter.test(c)) && c > -1 && c % 1 == 0 && c < a;
    }
    /** @type {number} */
    var previous = 9007199254740991;
    /** @type {!RegExp} */
    var matchLetter = /^(?:0|[1-9]\d*)$/;
    /** @type {function(number, number): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions, require) {
    var getNative = require(22);
    var nativeCreate = getNative(Object, "create");
    module.exports = nativeCreate;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {number} name
     * @param {number} type
     * @return {?}
     */
    function n(name, type) {
      return name === type || name !== name && type !== type;
    }
    /** @type {function(number, number): ?} */
    module.exports = n;
  }, function(mixin, canCreateDiscussions, require) {
    var matrix = require(96);
    var isArrayLikeObject = require(6);
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {function(this:Object, string): boolean} */
    var propertyIsEnumerable = ObjProto.propertyIsEnumerable;
    var m = matrix(function() {
      return arguments;
    }()) ? matrix : function(value) {
      return isArrayLikeObject(value) && hasOwnProperty.call(value, "callee") && !propertyIsEnumerable.call(value, "callee");
    };
    mixin.exports = m;
  }, function(canCreateDiscussions, self, require) {
    /**
     * @param {number} x
     * @param {number} y
     * @return {?}
     */
    function imageRef(x, y) {
      return {
        x : x,
        y : y,
        toVec2 : function() {
          return vec2.clone([this.x, this.y]);
        },
        toVec3 : function() {
          return vec3.clone([this.x, this.y, 1]);
        },
        round : function() {
          return this.x = this.x > 0 ? Math.floor(this.x + .5) : Math.floor(this.x - .5), this.y = this.y > 0 ? Math.floor(this.y + .5) : Math.floor(this.y - .5), this;
        }
      };
    }
    /**
     * @param {!Object} state
     * @param {?} result
     * @param {!Object} action
     * @return {undefined}
     */
    function reduce(state, result, action) {
      if (!action) {
        /** @type {!Object} */
        action = state;
      }
      var args = state.data;
      var i = args.length;
      var uri_schemes = action.data;
      for (; i--;) {
        /** @type {number} */
        uri_schemes[i] = args[i] < result ? 1 : 0;
      }
    }
    /**
     * @param {!Object} event
     * @param {number} i
     * @return {?}
     */
    function has(event, i) {
      if (!i) {
        /** @type {number} */
        i = 8;
      }
      var mask = event.data;
      var k = mask.length;
      /** @type {number} */
      var l = 8 - i;
      /** @type {number} */
      var n = 1 << i;
      /** @type {!Int32Array} */
      var result = new Int32Array(n);
      for (; k--;) {
        result[mask[k] >> l]++;
      }
      return result;
    }
    /**
     * @param {!Object} type
     * @param {number} s
     * @return {?}
     */
    function parse(type, s) {
      /**
       * @param {number} value
       * @param {number} length
       * @return {?}
       */
      function px(value, length) {
        var i;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        i = value;
        for (; i <= length; i++) {
          sum = sum + result[i];
        }
        return sum;
      }
      /**
       * @param {number} init
       * @param {number} end
       * @return {?}
       */
      function mx(init, end) {
        var i;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        i = init;
        for (; i <= end; i++) {
          /** @type {number} */
          sum = sum + i * result[i];
        }
        return sum;
      }
      /**
       * @return {?}
       */
      function position() {
        var p1;
        var p2;
        var p12;
        var k;
        var m1;
        var m2;
        var m12;
        /** @type {!Array} */
        var vet = [0];
        /** @type {number} */
        var max = (1 << s) - 1;
        result = has(type, s);
        /** @type {number} */
        k = 1;
        for (; k < max; k++) {
          p1 = px(0, k);
          p2 = px(k + 1, max);
          /** @type {number} */
          p12 = p1 * p2;
          if (0 === p12) {
            /** @type {number} */
            p12 = 1;
          }
          /** @type {number} */
          m1 = mx(0, k) * p2;
          /** @type {number} */
          m2 = mx(k + 1, max) * p1;
          /** @type {number} */
          m12 = m1 - m2;
          /** @type {number} */
          vet[k] = m12 * m12 / p12;
        }
        return that.a.maxIndex(vet);
      }
      if (!s) {
        /** @type {number} */
        s = 8;
      }
      var result;
      /** @type {number} */
      var delta = 8 - s;
      return position() << delta;
    }
    /**
     * @param {!Object} value
     * @param {!Object} data
     * @return {?}
     */
    function file(value, data) {
      var result = parse(value);
      return reduce(value, result, data), result;
    }
    /**
     * @param {!Array} points
     * @param {undefined} properties
     * @param {string} property
     * @return {?}
     */
    function add(points, properties, property) {
      /**
       * @param {!Object} value
       * @return {?}
       */
      function j(value) {
        /** @type {boolean} */
        var b = false;
        /** @type {number} */
        k = 0;
        for (; k < result.length; k++) {
          element = result[k];
          if (element.fits(value)) {
            element.add(value);
            /** @type {boolean} */
            b = true;
          }
        }
        return b;
      }
      var i;
      var k;
      var element;
      var a;
      /** @type {!Array} */
      var result = [];
      if (!property) {
        /** @type {string} */
        property = "rad";
      }
      /** @type {number} */
      i = 0;
      for (; i < points.length; i++) {
        a = m.a.createPoint(points[i], i, property);
        if (!j(a)) {
          result.push(m.a.create(a, properties));
        }
      }
      return result;
    }
    /**
     * @param {!Array} data
     * @param {number} end
     * @param {!Function} k
     * @return {?}
     */
    function handler(data, end, k) {
      var i;
      var c;
      var p;
      var j;
      /** @type {number} */
      var id = 0;
      /** @type {number} */
      var min = 0;
      /** @type {!Array} */
      var result = [];
      /** @type {number} */
      i = 0;
      for (; i < end; i++) {
        result[i] = {
          score : 0,
          item : null
        };
      }
      /** @type {number} */
      i = 0;
      for (; i < data.length; i++) {
        if ((c = k.apply(this, [data[i]])) > min) {
          p = result[id];
          p.score = c;
          p.item = data[i];
          /** @type {number} */
          min = Number.MAX_VALUE;
          /** @type {number} */
          j = 0;
          for (; j < end; j++) {
            if (result[j].score < min) {
              min = result[j].score;
              /** @type {number} */
              id = j;
            }
          }
        }
      }
      return result;
    }
    /**
     * @param {!Object} date
     * @param {!Object} n
     * @param {!NodeList} f
     * @return {undefined}
     */
    function d(date, n, f) {
      var r;
      /** @type {number} */
      var stxt3 = 0;
      var i = n.x;
      /** @type {number} */
      var cell_amount = Math.floor(date.length / 4);
      /** @type {number} */
      var numberOfActivationUnitsL2 = n.x / 2;
      /** @type {number} */
      var RED_INTEGER = 0;
      var s = n.x;
      for (; i < cell_amount;) {
        /** @type {number} */
        r = 0;
        for (; r < numberOfActivationUnitsL2; r++) {
          /** @type {number} */
          f[RED_INTEGER] = (.299 * date[4 * stxt3 + 0] + .587 * date[4 * stxt3 + 1] + .114 * date[4 * stxt3 + 2] + (.299 * date[4 * (stxt3 + 1) + 0] + .587 * date[4 * (stxt3 + 1) + 1] + .114 * date[4 * (stxt3 + 1) + 2]) + (.299 * date[4 * i + 0] + .587 * date[4 * i + 1] + .114 * date[4 * i + 2]) + (.299 * date[4 * (i + 1) + 0] + .587 * date[4 * (i + 1) + 1] + .114 * date[4 * (i + 1) + 2])) / 4;
          RED_INTEGER++;
          stxt3 = stxt3 + 2;
          i = i + 2;
        }
        stxt3 = stxt3 + s;
        i = i + s;
      }
    }
    /**
     * @param {string} s
     * @param {string} r
     * @param {!Function} g
     * @return {undefined}
     */
    function color(s, r, g) {
      var j;
      /** @type {number} */
      var _ccHeight = s.length / 4 | 0;
      if (g && g.singleChannel === true) {
        /** @type {number} */
        j = 0;
        for (; j < _ccHeight; j++) {
          r[j] = s[4 * j + 0];
        }
      } else {
        /** @type {number} */
        j = 0;
        for (; j < _ccHeight; j++) {
          /** @type {number} */
          r[j] = .299 * s[4 * j + 0] + .587 * s[4 * j + 1] + .114 * s[4 * j + 2];
        }
      }
    }
    /**
     * @param {!Object} elem
     * @param {!Object} inst
     * @return {undefined}
     */
    function data(elem, inst) {
      var result = elem.data;
      var i = elem.size.x;
      var map = inst.data;
      /** @type {number} */
      var j = 0;
      var index = i;
      var sources = result.length;
      /** @type {number} */
      var clientHeight = i / 2;
      /** @type {number} */
      var num_elements = 0;
      for (; index < sources;) {
        /** @type {number} */
        var targetOffsetHeight = 0;
        for (; targetOffsetHeight < clientHeight; targetOffsetHeight++) {
          /** @type {number} */
          map[num_elements] = Math.floor((result[j] + result[j + 1] + result[index] + result[index + 1]) / 4);
          num_elements++;
          j = j + 2;
          index = index + 2;
        }
        j = j + i;
        index = index + i;
      }
    }
    /**
     * @param {!Array} q
     * @param {!Array} context
     * @return {?}
     */
    function h(q, context) {
      var qq = q[0];
      var p = q[1];
      var c = q[2];
      /** @type {number} */
      var x = c * p;
      /** @type {number} */
      var s = x * (1 - Math.abs(qq / 60 % 2 - 1));
      /** @type {number} */
      var offset = c - x;
      /** @type {number} */
      var n = 0;
      /** @type {number} */
      var i = 0;
      /** @type {number} */
      var e = 0;
      return context = context || [0, 0, 0], qq < 60 ? (n = x, i = s) : qq < 120 ? (n = s, i = x) : qq < 180 ? (i = x, e = s) : qq < 240 ? (i = s, e = x) : qq < 300 ? (n = s, e = x) : qq < 360 && (n = x, e = s), context[0] = 255 * (n + offset) | 0, context[1] = 255 * (i + offset) | 0, context[2] = 255 * (e + offset) | 0, context;
    }
    /**
     * @param {number} n
     * @return {?}
     */
    function get(n) {
      var i;
      /** @type {!Array} */
      var num = [];
      /** @type {!Array} */
      var r = [];
      /** @type {number} */
      i = 1;
      for (; i < Math.sqrt(n) + 1; i++) {
        if (n % i == 0) {
          r.push(i);
          if (i !== n / i) {
            num.unshift(Math.floor(n / i));
          }
        }
      }
      return r.concat(num);
    }
    /**
     * @param {!NodeList} arr
     * @param {!NodeList} version
     * @return {?}
     */
    function $(arr, version) {
      /** @type {number} */
      var j = 0;
      /** @type {number} */
      var i = 0;
      /** @type {!Array} */
      var el = [];
      for (; j < arr.length && i < version.length;) {
        if (arr[j] === version[i]) {
          el.push(arr[j]);
          j++;
          i++;
        } else {
          if (arr[j] > version[i]) {
            i++;
          } else {
            j++;
          }
        }
      }
      return el;
    }
    /**
     * @param {?} patchSize
     * @param {!Object} fragment
     * @return {?}
     */
    function render(patchSize, fragment) {
      /**
       * @param {!Object} a
       * @return {?}
       */
      function resolve(a) {
        /** @type {number} */
        var index = 0;
        var i = a[Math.floor(a.length / 2)];
        for (; index < a.length - 1 && a[index] < v;) {
          index++;
        }
        return index > 0 && (i = Math.abs(a[index] - v) > Math.abs(a[index - 1] - v) ? a[index - 1] : a[index]), v / i < nrOfPatchesList[nrOfPatchesIdx + 1] / nrOfPatchesList[nrOfPatchesIdx] && v / i > nrOfPatchesList[nrOfPatchesIdx - 1] / nrOfPatchesList[nrOfPatchesIdx] ? {
          x : i,
          y : i
        } : null;
      }
      var token;
      var m = get(fragment.x);
      var a = get(fragment.y);
      /** @type {number} */
      var b = Math.max(fragment.x, fragment.y);
      var u = $(m, a);
      /** @type {!Array} */
      var nrOfPatchesList = [8, 10, 15, 20, 32, 60, 80];
      var nrOfPatchesMap = {
        "x-small" : 5,
        small : 4,
        medium : 3,
        large : 2,
        "x-large" : 1
      };
      var nrOfPatchesIdx = nrOfPatchesMap[patchSize] || nrOfPatchesMap.medium;
      var nrOfPatches = nrOfPatchesList[nrOfPatchesIdx];
      /** @type {number} */
      var v = Math.floor(b / nrOfPatches);
      return token = resolve(u), token || (token = resolve(get(b))) || (token = resolve(get(v * nrOfPatches))), token;
    }
    /**
     * @param {string} component
     * @return {?}
     */
    function find(component) {
      return {
        value : parseFloat(component),
        unit : (component.indexOf("%"), component.length, "%")
      };
    }
    /**
     * @param {number} cols
     * @param {number} type
     * @param {!Object} bindings
     * @return {?}
     */
    function init(cols, type, bindings) {
      var args = {
        width : cols,
        height : type
      };
      var obj = Object.keys(bindings).reduce(function(memo, i) {
        var x = bindings[i];
        var val = find(x);
        var value = obj[i](val, args);
        return memo[i] = value, memo;
      }, {});
      return {
        sx : obj.left,
        sy : obj.top,
        sw : obj.right - obj.left,
        sh : obj.bottom - obj.top
      };
    }
    var m = require(50);
    var that = require(3);
    /** @type {function(number, number): ?} */
    self.b = imageRef;
    /** @type {function(!Object, !Object): ?} */
    self.f = file;
    /** @type {function(!Array, undefined, string): ?} */
    self.g = add;
    /** @type {function(!Array, number, !Function): ?} */
    self.h = handler;
    /** @type {function(!Object, !Object, !NodeList): undefined} */
    self.c = d;
    /** @type {function(string, string, !Function): undefined} */
    self.d = color;
    /** @type {function(!Object, !Object): undefined} */
    self.i = data;
    /** @type {function(!Array, !Array): ?} */
    self.a = h;
    /** @type {function(?, !Object): ?} */
    self.e = render;
    /** @type {function(number, number, !Object): ?} */
    self.j = init;
    var vec2 = {
      clone : require(7)
    };
    var vec3 = {
      clone : require(83)
    };
    var obj = {
      top : function(e, t) {
        if ("%" === e.unit) {
          return Math.floor(t.height * (e.value / 100));
        }
      },
      right : function(e, prop) {
        if ("%" === e.unit) {
          return Math.floor(prop.width - prop.width * (e.value / 100));
        }
      },
      bottom : function(e, n) {
        if ("%" === e.unit) {
          return Math.floor(n.height - n.height * (e.value / 100));
        }
      },
      left : function(value, dimension) {
        if ("%" === value.unit) {
          return Math.floor(dimension.width * (value.value / 100));
        }
      }
    };
  }, function(canCreateDiscussions, defaultTagAttributes, $) {
    /**
     * @param {number} size
     * @param {!Object} data
     * @param {!Function} ArrayType
     * @param {!Function} initialize
     * @return {undefined}
     */
    function ImageWrapper(size, data, ArrayType, initialize) {
      if (data) {
        /** @type {!Object} */
        this.data = data;
      } else {
        if (ArrayType) {
          this.data = new ArrayType(size.x * size.y);
          if (ArrayType === Array && initialize) {
            a.a.init(this.data, 0);
          }
        } else {
          /** @type {!Uint8Array} */
          this.data = new Uint8Array(size.x * size.y);
          if (Uint8Array === Array && initialize) {
            a.a.init(this.data, 0);
          }
        }
      }
      /** @type {number} */
      this.size = size;
    }
    var td = $(53);
    var t = $(19);
    var a = $(3);
    var appInfo = {
      clone : $(7)
    };
    /**
     * @param {!Object} imgRef
     * @param {number} border
     * @return {?}
     */
    ImageWrapper.prototype.inImageWithBorder = function(imgRef, border) {
      return imgRef.x >= border && imgRef.y >= border && imgRef.x < this.size.x - border && imgRef.y < this.size.y - border;
    };
    /**
     * @param {!Object} inImg
     * @param {number} x
     * @param {number} y
     * @return {?}
     */
    ImageWrapper.sample = function(inImg, x, y) {
      /** @type {number} */
      var lx = Math.floor(x);
      /** @type {number} */
      var ly = Math.floor(y);
      var w = inImg.size.x;
      /** @type {number} */
      var base = ly * inImg.size.x + lx;
      var e = inImg.data[base + 0];
      var start = inImg.data[base + 1];
      var index = inImg.data[base + w];
      var i = inImg.data[base + w + 1];
      /** @type {number} */
      var month = e - start;
      return x = x - lx, y = y - ly, Math.floor(x * (y * (month - index + i) - month) + y * (index - e) + e);
    };
    /**
     * @param {!Array} array
     * @return {undefined}
     */
    ImageWrapper.clearArray = function(array) {
      var i = array.length;
      for (; i--;) {
        /** @type {number} */
        array[i] = 0;
      }
    };
    /**
     * @param {?} dcl
     * @param {?} x
     * @return {?}
     */
    ImageWrapper.prototype.subImage = function(dcl, x) {
      return new td.a(dcl, x, this);
    };
    /**
     * @param {!Object} imageWrapper
     * @param {!Object} from
     * @return {undefined}
     */
    ImageWrapper.prototype.subImageAsCopy = function(imageWrapper, from) {
      var x;
      var y;
      var lenY = imageWrapper.size.y;
      var mapWidth = imageWrapper.size.x;
      /** @type {number} */
      x = 0;
      for (; x < mapWidth; x++) {
        /** @type {number} */
        y = 0;
        for (; y < lenY; y++) {
          imageWrapper.data[y * mapWidth + x] = this.data[(from.y + y) * this.size.x + from.x + x];
        }
      }
    };
    /**
     * @param {!Object} event
     * @return {undefined}
     */
    ImageWrapper.prototype.copyTo = function(event) {
      var i = this.data.length;
      var d = this.data;
      var p = event.data;
      for (; i--;) {
        p[i] = d[i];
      }
    };
    /**
     * @param {number} x
     * @param {number} y
     * @return {?}
     */
    ImageWrapper.prototype.get = function(x, y) {
      return this.data[y * this.size.x + x];
    };
    /**
     * @param {number} x
     * @param {number} y
     * @return {?}
     */
    ImageWrapper.prototype.getSafe = function(x, y) {
      var i;
      if (!this.indexMapping) {
        this.indexMapping = {
          x : [],
          y : []
        };
        /** @type {number} */
        i = 0;
        for (; i < this.size.x; i++) {
          /** @type {number} */
          this.indexMapping.x[i] = i;
          /** @type {number} */
          this.indexMapping.x[i + this.size.x] = i;
        }
        /** @type {number} */
        i = 0;
        for (; i < this.size.y; i++) {
          /** @type {number} */
          this.indexMapping.y[i] = i;
          /** @type {number} */
          this.indexMapping.y[i + this.size.y] = i;
        }
      }
      return this.data[this.indexMapping.y[y + this.size.y] * this.size.x + this.indexMapping.x[x + this.size.x]];
    };
    /**
     * @param {number} i
     * @param {number} x
     * @param {string} y
     * @return {?}
     */
    ImageWrapper.prototype.set = function(i, x, y) {
      return this.data[x * this.size.x + i] = y, this;
    };
    /**
     * @return {undefined}
     */
    ImageWrapper.prototype.zeroBorder = function() {
      var i;
      var width = this.size.x;
      var height = this.size.y;
      var items = this.data;
      /** @type {number} */
      i = 0;
      for (; i < width; i++) {
        /** @type {number} */
        items[i] = items[(height - 1) * width + i] = 0;
      }
      /** @type {number} */
      i = 1;
      for (; i < height - 1; i++) {
        /** @type {number} */
        items[i * width] = items[i * width + (width - 1)] = 0;
      }
    };
    /**
     * @return {undefined}
     */
    ImageWrapper.prototype.invert = function() {
      var array = this.data;
      var i = array.length;
      for (; i--;) {
        /** @type {number} */
        array[i] = array[i] ? 0 : 1;
      }
    };
    /**
     * @param {!Object} kernel
     * @return {undefined}
     */
    ImageWrapper.prototype.convolve = function(kernel) {
      var x;
      var y;
      var kx;
      var ky;
      /** @type {number} */
      var kSize = kernel.length / 2 | 0;
      /** @type {number} */
      var cp2y = 0;
      /** @type {number} */
      y = 0;
      for (; y < this.size.y; y++) {
        /** @type {number} */
        x = 0;
        for (; x < this.size.x; x++) {
          /** @type {number} */
          cp2y = 0;
          /** @type {number} */
          ky = -kSize;
          for (; ky <= kSize; ky++) {
            /** @type {number} */
            kx = -kSize;
            for (; kx <= kSize; kx++) {
              /** @type {number} */
              cp2y = cp2y + kernel[ky + kSize][kx + kSize] * this.getSafe(x + kx, y + ky);
            }
          }
          /** @type {number} */
          this.data[y * this.size.x + x] = cp2y;
        }
      }
    };
    /**
     * @param {number} labelcount
     * @return {?}
     */
    ImageWrapper.prototype.moments = function(labelcount) {
      var x;
      var y;
      var val;
      var ysq;
      var i;
      var label;
      var mu11;
      var mu02;
      var mu20;
      var Y;
      var value;
      var tmp;
      var d = this.data;
      var height = this.size.y;
      var width = this.size.x;
      /** @type {!Array} */
      var labelsum = [];
      /** @type {!Array} */
      var result = [];
      /** @type {number} */
      var PI = Math.PI;
      /** @type {number} */
      var PI_4 = PI / 4;
      if (labelcount <= 0) {
        return result;
      }
      /** @type {number} */
      i = 0;
      for (; i < labelcount; i++) {
        labelsum[i] = {
          m00 : 0,
          m01 : 0,
          m10 : 0,
          m11 : 0,
          m02 : 0,
          m20 : 0,
          theta : 0,
          rad : 0
        };
      }
      /** @type {number} */
      y = 0;
      for (; y < height; y++) {
        /** @type {number} */
        ysq = y * y;
        /** @type {number} */
        x = 0;
        for (; x < width; x++) {
          if ((val = d[y * width + x]) > 0) {
            label = labelsum[val - 1];
            label.m00 += 1;
            label.m01 += y;
            label.m10 += x;
            label.m11 += x * y;
            label.m02 += ysq;
            label.m20 += x * x;
          }
        }
      }
      /** @type {number} */
      i = 0;
      for (; i < labelcount; i++) {
        label = labelsum[i];
        if (!(isNaN(label.m00) || 0 === label.m00)) {
          /** @type {number} */
          Y = label.m10 / label.m00;
          /** @type {number} */
          value = label.m01 / label.m00;
          /** @type {number} */
          mu11 = label.m11 / label.m00 - Y * value;
          /** @type {number} */
          mu02 = label.m02 / label.m00 - value * value;
          /** @type {number} */
          mu20 = label.m20 / label.m00 - Y * Y;
          /** @type {number} */
          tmp = (mu02 - mu20) / (2 * mu11);
          /** @type {number} */
          tmp = .5 * Math.atan(tmp) + (mu11 >= 0 ? PI_4 : -PI_4) + PI;
          /** @type {number} */
          label.theta = (180 * tmp / PI + 90) % 180 - 90;
          if (label.theta < 0) {
            label.theta += 180;
          }
          /** @type {number} */
          label.rad = tmp > PI ? tmp - PI : tmp;
          label.vec = appInfo.clone([Math.cos(tmp), Math.sin(tmp)]);
          result.push(label);
        }
      }
      return result;
    };
    /**
     * @param {!HTMLCanvasElement} canvas
     * @param {number} scale
     * @return {undefined}
     */
    ImageWrapper.prototype.show = function(canvas, scale) {
      var ctx;
      var pixelData;
      var _ref1;
      var plane_w;
      var cameraXCell;
      var x;
      var y;
      if (!scale) {
        /** @type {number} */
        scale = 1;
      }
      ctx = canvas.getContext("2d");
      canvas.width = this.size.x;
      canvas.height = this.size.y;
      pixelData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      _ref1 = pixelData.data;
      /** @type {number} */
      plane_w = 0;
      /** @type {number} */
      y = 0;
      for (; y < this.size.y; y++) {
        /** @type {number} */
        x = 0;
        for (; x < this.size.x; x++) {
          /** @type {number} */
          cameraXCell = y * this.size.x + x;
          /** @type {number} */
          plane_w = this.get(x, y) * scale;
          /** @type {number} */
          _ref1[4 * cameraXCell + 0] = plane_w;
          /** @type {number} */
          _ref1[4 * cameraXCell + 1] = plane_w;
          /** @type {number} */
          _ref1[4 * cameraXCell + 2] = plane_w;
          /** @type {number} */
          _ref1[4 * cameraXCell + 3] = 255;
        }
      }
      ctx.putImageData(pixelData, 0, 0);
    };
    /**
     * @param {!HTMLCanvasElement} context
     * @param {number} scale
     * @param {!Object} from
     * @return {undefined}
     */
    ImageWrapper.prototype.overlay = function(context, scale, from) {
      if (!scale || scale < 0 || scale > 360) {
        /** @type {number} */
        scale = 360;
      }
      /** @type {!Array} */
      var style = [0, 1, 1];
      /** @type {!Array} */
      var artistTrack = [0, 0, 0];
      /** @type {!Array} */
      var __ = [255, 255, 255];
      /** @type {!Array} */
      var c = [0, 0, 0];
      /** @type {!Array} */
      var s = [];
      var ctx = context.getContext("2d");
      var result = ctx.getImageData(from.x, from.y, this.size.x, this.size.y);
      var arr = result.data;
      var i = this.data.length;
      for (; i--;) {
        /** @type {number} */
        style[0] = this.data[i] * scale;
        s = style[0] <= 0 ? __ : style[0] >= 360 ? c : $.i(t.a)(style, artistTrack);
        arr[4 * i + 0] = s[0];
        arr[4 * i + 1] = s[1];
        arr[4 * i + 2] = s[2];
        /** @type {number} */
        arr[4 * i + 3] = 255;
      }
      ctx.putImageData(result, from.x, from.y);
    };
    /** @type {function(number, !Object, !Function, !Function): undefined} */
    defaultTagAttributes.a = ImageWrapper;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!NodeList} constructor
     * @param {number} name
     * @param {number} value
     * @return {undefined}
     */
    function extend(constructor, name, value) {
      if ("__proto__" == name && defineProperty) {
        defineProperty(constructor, name, {
          configurable : true,
          enumerable : true,
          value : value,
          writable : true
        });
      } else {
        /** @type {number} */
        constructor[name] = value;
      }
    }
    var defineProperty = __webpack_require__(37);
    /** @type {function(!NodeList, number, number): undefined} */
    module.exports = extend;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {?} el
     * @param {number} context
     * @return {?}
     */
    function apply(el, context) {
      var classes = $(el, context);
      return isArray(classes) ? classes : void 0;
    }
    var isArray = require(97);
    var $ = require(120);
    /** @type {function(?, number): ?} */
    module.exports = apply;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {number} key
     * @return {?}
     */
    function toKey(key) {
      if ("string" == typeof key || isSymbol(key)) {
        return key;
      }
      /** @type {string} */
      var keyString = key + "";
      return "0" == keyString && 1 / key == -Infinity ? "-0" : keyString;
    }
    var isSymbol = require(27);
    /** @type {number} */
    var Infinity = 1 / 0;
    /** @type {function(number): ?} */
    module.exports = toKey;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {!Object} value
     * @return {?}
     */
    function r(value) {
      return null != value && isNumber(value.length) && !isPromise(value);
    }
    var isPromise = require(25);
    var isNumber = require(26);
    /** @type {function(!Object): ?} */
    task.exports = r;
  }, function(task, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} value
     * @return {?}
     */
    function r(value) {
      if (!isArray(value)) {
        return false;
      }
      var name = normalize(value);
      return name == showUserAgentCSS || name == expandShorthandProps || name == UW_CO_APPWRK_UW_CO_CONFIRM_APP || name == UW_CO_JOBSRCHDW_UW_CO_DW_SRCHBTN;
    }
    var normalize = __webpack_require__(8);
    var isArray = __webpack_require__(0);
    /** @type {string} */
    var UW_CO_APPWRK_UW_CO_CONFIRM_APP = "[object AsyncFunction]";
    /** @type {string} */
    var showUserAgentCSS = "[object Function]";
    /** @type {string} */
    var expandShorthandProps = "[object GeneratorFunction]";
    /** @type {string} */
    var UW_CO_JOBSRCHDW_UW_CO_DW_SRCHBTN = "[object Proxy]";
    /** @type {function(?): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {number} length
     * @return {?}
     */
    function n(length) {
      return "number" == typeof length && length > -1 && length % 1 == 0 && length <= MAX_ARRAY_INDEX;
    }
    /** @type {number} */
    var MAX_ARRAY_INDEX = 9007199254740991;
    /** @type {function(number): ?} */
    module.exports = n;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {?} x
     * @return {?}
     */
    function r(x) {
      return "symbol" == typeof x || isNumber(x) && isPromise(x) == o;
    }
    var isPromise = require(8);
    var isNumber = require(6);
    /** @type {string} */
    var o = "[object Symbol]";
    /** @type {function(?): ?} */
    task.exports = r;
  }, function(mixin, canCreateDiscussions, require) {
    var compare = require(100);
    var balanced = require(116);
    var m = balanced(function(correct_result, decompressed_result, callback) {
      compare(correct_result, decompressed_result, callback);
    });
    mixin.exports = m;
  }, function(mixin, canCreateDiscussions) {
    /**
     * @param {!Object} module
     * @return {?}
     */
    mixin.exports = function(module) {
      return module.webpackPolyfill || (module.deprecate = function() {
      }, module.paths = [], module.children || (module.children = []), Object.defineProperty(module, "loaded", {
        enumerable : true,
        get : function() {
          return module.l;
        }
      }), Object.defineProperty(module, "id", {
        enumerable : true,
        get : function() {
          return module.i;
        }
      }), module.webpackPolyfill = 1), module;
    };
  }, function(canCreateDiscussions, exports, n) {
    var Tracer = {
      searchDirections : [[0, 1], [1, 1], [1, 0], [1, -1], [0, -1], [-1, -1], [-1, 0], [-1, 1]],
      create : function(target, props) {
        /**
         * @param {!Object} current
         * @param {!Object} s
         * @param {number} o
         * @param {number} v
         * @return {?}
         */
        function trace(current, s, o, v) {
          var o;
          var y;
          var x;
          /** @type {number} */
          o = 0;
          for (; o < 7; o++) {
            if (y = current.cy + searchDirections[current.dir][0], x = current.cx + searchDirections[current.dir][1], i = y * sizeX + x, str[i] === s && (0 === arr[i] || arr[i] === o)) {
              return arr[i] = o, current.cy = y, current.cx = x, true;
            }
            if (0 === arr[i]) {
              /** @type {number} */
              arr[i] = v;
            }
            /** @type {number} */
            current.dir = (current.dir + 1) % 8;
          }
          return false;
        }
        /**
         * @param {number} x
         * @param {number} y
         * @param {string} dir
         * @return {?}
         */
        function vertex2D(x, y, dir) {
          return {
            dir : dir,
            x : x,
            y : y,
            next : null,
            prev : null
          };
        }
        /**
         * @param {number} sy
         * @param {number} sx
         * @param {number} label
         * @param {!Object} color
         * @param {number} edgelabel
         * @return {?}
         */
        function contourTracing(sy, sx, label, color, edgelabel) {
          var Cv;
          var P;
          var ldir;
          /** @type {null} */
          var Fv = null;
          var current = {
            cx : sx,
            cy : sy,
            dir : 0
          };
          if (trace(current, color, label, edgelabel)) {
            Fv = vertex2D(sx, sy, current.dir);
            Cv = Fv;
            /** @type {number} */
            ldir = current.dir;
            P = vertex2D(current.cx, current.cy, 0);
            P.prev = Cv;
            Cv.next = P;
            /** @type {null} */
            P.next = null;
            Cv = P;
            do {
              /** @type {number} */
              current.dir = (current.dir + 6) % 8;
              trace(current, color, label, edgelabel);
              if (ldir !== current.dir) {
                /** @type {number} */
                Cv.dir = current.dir;
                P = vertex2D(current.cx, current.cy, 0);
                P.prev = Cv;
                Cv.next = P;
                /** @type {null} */
                P.next = null;
                Cv = P;
              } else {
                /** @type {number} */
                Cv.dir = ldir;
                Cv.x = current.cx;
                Cv.y = current.cy;
              }
              /** @type {number} */
              ldir = current.dir;
            } while (current.cx !== sx || current.cy !== sy);
            Fv.prev = Cv.prev;
            Cv.prev.next = Fv;
          }
          return Fv;
        }
        var i;
        var str = target.data;
        var arr = props.data;
        var searchDirections = this.searchDirections;
        var sizeX = target.size.x;
        return {
          trace : function(target, name, value, e) {
            return trace(target, name, value, e);
          },
          contourTracing : function(sy, sx, label, color, edgelabel) {
            return contourTracing(sy, sx, label, color, edgelabel);
          }
        };
      }
    };
    exports.a = Tracer;
  }, function(canCreateDiscussions, defaultTagAttributes, require) {
    /**
     * @return {undefined}
     */
    function Code39Reader() {
      o.a.call(this);
    }
    var o = require(1);
    var store = require(3);
    var properties = {
      ALPHABETH_STRING : {
        value : "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%"
      },
      ALPHABET : {
        value : [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 45, 46, 32, 42, 36, 47, 43, 37]
      },
      CHARACTER_ENCODINGS : {
        value : [52, 289, 97, 352, 49, 304, 112, 37, 292, 100, 265, 73, 328, 25, 280, 88, 13, 268, 76, 28, 259, 67, 322, 19, 274, 82, 7, 262, 70, 22, 385, 193, 448, 145, 400, 208, 133, 388, 196, 148, 168, 162, 138, 42]
      },
      ASTERISK : {
        value : 148
      },
      FORMAT : {
        value : "code_39",
        writeable : false
      }
    };
    /** @type {!Object} */
    Code39Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    Code39Reader.prototype.constructor = Code39Reader;
    /**
     * @return {?}
     */
    Code39Reader.prototype._decode = function() {
      var pt;
      var lastStart;
      var pattern;
      var nextStart;
      var self = this;
      /** @type {!Array} */
      var counters = [0, 0, 0, 0, 0, 0, 0, 0, 0];
      /** @type {!Array} */
      var result = [];
      var start = self._findStart();
      if (!start) {
        return null;
      }
      nextStart = self._nextSet(self._row, start.end);
      do {
        if (counters = self._toCounters(nextStart, counters), (pattern = self._toPattern(counters)) < 0) {
          return null;
        }
        if ((pt = self._patternToChar(pattern)) < 0) {
          return null;
        }
        result.push(pt);
        lastStart = nextStart;
        nextStart = nextStart + store.a.sum(counters);
        nextStart = self._nextSet(self._row, nextStart);
      } while ("*" !== pt);
      return result.pop(), result.length && self._verifyTrailingWhitespace(lastStart, nextStart, counters) ? {
        code : result.join(""),
        start : start.start,
        end : nextStart,
        startInfo : start,
        decodedCodes : result
      } : null;
    };
    /**
     * @param {!Object} lastStart
     * @param {!Object} nextStart
     * @param {!Array} counters
     * @return {?}
     */
    Code39Reader.prototype._verifyTrailingWhitespace = function(lastStart, nextStart, counters) {
      var patternSize = store.a.sum(counters);
      return 3 * (nextStart - lastStart - patternSize) >= patternSize;
    };
    /**
     * @param {!Object} pattern
     * @return {?}
     */
    Code39Reader.prototype._patternToChar = function(pattern) {
      var i;
      var self = this;
      /** @type {number} */
      i = 0;
      for (; i < self.CHARACTER_ENCODINGS.length; i++) {
        if (self.CHARACTER_ENCODINGS[i] === pattern) {
          return String.fromCharCode(self.ALPHABET[i]);
        }
      }
      return -1;
    };
    /**
     * @param {!Array} counters
     * @param {number} current
     * @return {?}
     */
    Code39Reader.prototype._findNextWidth = function(counters, current) {
      var i;
      /** @type {number} */
      var minWidth = Number.MAX_VALUE;
      /** @type {number} */
      i = 0;
      for (; i < counters.length; i++) {
        if (counters[i] < minWidth && counters[i] > current) {
          minWidth = counters[i];
        }
      }
      return minWidth;
    };
    /**
     * @param {!Array} counters
     * @return {?}
     */
    Code39Reader.prototype._toPattern = function(counters) {
      var pattern;
      var pos;
      var len = counters.length;
      /** @type {number} */
      var maxNarrowWidth = 0;
      var i = len;
      /** @type {number} */
      var sum = 0;
      var self = this;
      for (; i > 3;) {
        maxNarrowWidth = self._findNextWidth(counters, maxNarrowWidth);
        /** @type {number} */
        i = 0;
        /** @type {number} */
        pattern = 0;
        /** @type {number} */
        pos = 0;
        for (; pos < len; pos++) {
          if (counters[pos] > maxNarrowWidth) {
            /** @type {number} */
            pattern = pattern | 1 << len - 1 - pos;
            i++;
            sum = sum + counters[pos];
          }
        }
        if (3 === i) {
          /** @type {number} */
          pos = 0;
          for (; pos < len && i > 0; pos++) {
            if (counters[pos] > maxNarrowWidth && (i--, 2 * counters[pos] >= sum)) {
              return -1;
            }
          }
          return pattern;
        }
      }
      return -1;
    };
    /**
     * @return {?}
     */
    Code39Reader.prototype._findStart = function() {
      var i;
      var j;
      var e;
      var self = this;
      var point = self._nextSet(self._row);
      var b = point;
      /** @type {!Array} */
      var counter = [0, 0, 0, 0, 0, 0, 0, 0, 0];
      /** @type {number} */
      var name = 0;
      /** @type {boolean} */
      var isWhite = false;
      i = point;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            if (self._toPattern(counter) === self.ASTERISK && (e = Math.floor(Math.max(0, b - (i - b) / 4)), self._matchRange(e, b, 0))) {
              return {
                start : b,
                end : i
              };
            }
            b = b + (counter[0] + counter[1]);
            /** @type {number} */
            j = 0;
            for (; j < 7; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[7] = 0;
            /** @type {number} */
            counter[8] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /** @type {function(): undefined} */
    defaultTagAttributes.a = Code39Reader;
  }, function(mixin, canCreateDiscussions) {
    /**
     * @param {!Object} quat_1
     * @param {number} quat_2
     * @return {?}
     */
    function multiplyQuaternion(quat_1, quat_2) {
      return quat_1[0] * quat_2[0] + quat_1[1] * quat_2[1];
    }
    /** @type {function(!Object, number): ?} */
    mixin.exports = multiplyQuaternion;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    var getNative = __webpack_require__(22);
    var root = __webpack_require__(5);
    var Map = getNative(root, "Map");
    module.exports = Map;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {string} o
     * @return {undefined}
     */
    function self(o) {
      /** @type {number} */
      var j = -1;
      var r_len = null == o ? 0 : o.length;
      this.clear();
      for (; ++j < r_len;) {
        var a = o[j];
        this.set(a[0], a[1]);
      }
    }
    var listCacheClear = __webpack_require__(138);
    var method = __webpack_require__(139);
    var hashGet = __webpack_require__(140);
    var has = __webpack_require__(141);
    var cookie = __webpack_require__(142);
    self.prototype.clear = listCacheClear;
    self.prototype.delete = method;
    self.prototype.get = hashGet;
    self.prototype.has = has;
    self.prototype.set = cookie;
    /** @type {function(string): undefined} */
    module.exports = self;
  }, function(mixin, canCreateDiscussions, require) {
    /**
     * @param {!Array} p
     * @param {number} i
     * @param {?} e
     * @return {undefined}
     */
    function m(p, i, e) {
      if (!((void 0 === e || requestAnimationFrame(p[i], e)) && (void 0 !== e || i in p))) {
        r(p, i, e);
      }
    }
    var r = require(21);
    var requestAnimationFrame = require(17);
    /** @type {function(!Array, number, ?): undefined} */
    mixin.exports = m;
  }, function(task, canCreateDiscussions, n) {
    /**
     * @param {!Array} a
     * @param {number} i
     * @param {?} e
     * @return {undefined}
     */
    function r(a, i, e) {
      var b = a[i];
      if (!(hasOwn.call(a, i) && p(b, e) && (void 0 !== e || i in a))) {
        f(a, i, e);
      }
    }
    var f = n(21);
    var p = n(17);
    var OP = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwn = OP.hasOwnProperty;
    /** @type {function(!Array, number, ?): undefined} */
    task.exports = r;
  }, function(module, canCreateDiscussions, saveNotifs) {
    var $defineProperty = saveNotifs(22);
    var storeMixin = function() {
      try {
        var hasKey = $defineProperty(Object, "defineProperty");
        return hasKey({}, "", {}), hasKey;
      } catch (t) {
      }
    }();
    module.exports = storeMixin;
  }, function(module, gen34_options, moment) {
    (function(global) {
      var freeGlobal = "object" == typeof global && global && global.Object === Object && global;
      module.exports = freeGlobal;
    }).call(gen34_options, moment(47));
  }, function(module, canCreateDiscussions, __webpack_require__) {
    var overArg = __webpack_require__(147);
    var getPrototype = overArg(Object.getPrototypeOf, Object);
    module.exports = getPrototype;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Object} b
     * @return {?}
     */
    function n(b) {
      var obj = b && b.constructor;
      return b === ("function" == typeof obj && obj.prototype || oproto);
    }
    var oproto = Object.prototype;
    /** @type {function(!Object): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions, n) {
    /**
     * @param {!NodeList} val
     * @param {number} m
     * @param {!Array} d
     * @return {?}
     */
    function run(val, m, d) {
      return m = min(void 0 === m ? val.length - 1 : m, 0), function() {
        /** @type {!Arguments} */
        var b = arguments;
        /** @type {number} */
        var i = -1;
        /** @type {number} */
        var l = min(b.length - m, 0);
        /** @type {!Array} */
        var a = Array(l);
        for (; ++i < l;) {
          a[i] = b[m + i];
        }
        /** @type {number} */
        i = -1;
        /** @type {!Array} */
        var c = Array(m + 1);
        for (; ++i < m;) {
          c[i] = b[i];
        }
        return c[m] = d(a), f(val, this, c);
      };
    }
    var f = n(87);
    /** @type {function(...?): number} */
    var min = Math.max;
    /** @type {function(!NodeList, number, !Array): ?} */
    module.exports = run;
  }, function(mixin, canCreateDiscussions, prepare) {
    var u = prepare(106);
    var a = prepare(148);
    var m = a(u);
    mixin.exports = m;
  }, function(mixin, canCreateDiscussions) {
    /**
     * @param {?} d
     * @return {?}
     */
    function m(d) {
      return d;
    }
    /** @type {function(?): ?} */
    mixin.exports = m;
  }, function(context, exports, parse) {
    (function(module) {
      var root = parse(5);
      var browser = parse(163);
      var freeExports = "object" == typeof exports && exports && !exports.nodeType && exports;
      var freeModule = freeExports && "object" == typeof module && module && !module.nodeType && module;
      var moduleExports = freeModule && freeModule.exports === freeExports;
      var Buffer = moduleExports ? root.Buffer : void 0;
      var runtime = Buffer ? Buffer.isBuffer : void 0;
      var f = runtime || browser;
      module.exports = f;
    }).call(exports, parse(29)(context));
  }, function(mixin, canCreateDiscussions, __webpack_require__) {
    var tmp = __webpack_require__(98);
    var from_cache = __webpack_require__(109);
    var nodeUtil = __webpack_require__(145);
    var object = nodeUtil && nodeUtil.isTypedArray;
    var m = object ? from_cache(object) : tmp;
    mixin.exports = m;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {?} value
     * @return {?}
     */
    function r(value) {
      return isNumber(value) ? cp(value, true) : stringify(value);
    }
    var cp = require(88);
    var stringify = require(99);
    var isNumber = require(24);
    /** @type {function(?): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions) {
    var g;
    g = function() {
      return this;
    }();
    try {
      g = g || Function("return this")() || (0, eval)("this");
    } catch (t) {
      if ("object" == typeof window) {
        /** @type {!Window} */
        g = window;
      }
    }
    module.exports = g;
  }, function(canCreateDiscussions, $scope, $) {
    /**
     * @param {string} desc
     * @return {undefined}
     */
    function cb(desc) {
      fn(desc);
      console = k.a.create(options.decoder, data);
    }
    /**
     * @param {!Function} resolve
     * @return {undefined}
     */
    function build(resolve) {
      var video;
      if ("VideoStream" === options.inputStream.type) {
        /** @type {!Element} */
        video = document.createElement("video");
        that = clonedI.a.createVideoStream(video);
      } else {
        if ("ImageStream" === options.inputStream.type) {
          that = clonedI.a.createImageStream();
        } else {
          if ("LiveStream" === options.inputStream.type) {
            var el = createElement();
            if (el) {
              if (!(video = el.querySelector("video"))) {
                /** @type {!Element} */
                video = document.createElement("video");
                el.appendChild(video);
              }
            }
            that = clonedI.a.createLiveStream(video);
            subject.a.request(video, options.inputStream.constraints).then(function() {
              that.trigger("canrecord");
            }).catch(function(gwServerCredentials_) {
              return resolve(gwServerCredentials_);
            });
          }
        }
      }
      that.setAttribute("preload", "auto");
      that.setInputStream(options.inputStream);
      that.addEventListener("canrecord", dialog.bind(void 0, resolve));
    }
    /**
     * @return {?}
     */
    function createElement() {
      var item = options.inputStream.target;
      if (item && item.nodeName && 1 === item.nodeType) {
        return item;
      }
      /** @type {string} */
      var id = "string" == typeof item ? item : "#interactive.viewport";
      return document.querySelector(id);
    }
    /**
     * @param {?} callback
     * @return {undefined}
     */
    function dialog(callback) {
      self.a.checkImageConstraints(that, options.locator);
      init(options);
      f = AvatarsIO.a.create(that, _canvas.dom.image);
      map(options.numOfWorkers, function() {
        if (0 === options.numOfWorkers) {
          cb();
        }
        func(callback);
      });
    }
    /**
     * @param {?} callback
     * @return {undefined}
     */
    function func(callback) {
      that.play();
      callback();
    }
    /**
     * @return {undefined}
     */
    function init() {
      if ("undefined" != typeof document) {
        var list = createElement();
        if (_canvas.dom.image = document.querySelector("canvas.imgBuffer"), _canvas.dom.image || (_canvas.dom.image = document.createElement("canvas"), _canvas.dom.image.className = "imgBuffer", list && "ImageStream" === options.inputStream.type && list.appendChild(_canvas.dom.image)), _canvas.ctx.image = _canvas.dom.image.getContext("2d"), _canvas.dom.image.width = that.getCanvasSize().x, _canvas.dom.image.height = that.getCanvasSize().y, _canvas.dom.overlay = document.querySelector("canvas.drawingBuffer"), 
        !_canvas.dom.overlay) {
          /** @type {!Element} */
          _canvas.dom.overlay = document.createElement("canvas");
          /** @type {string} */
          _canvas.dom.overlay.className = "drawingBuffer";
          if (list) {
            list.appendChild(_canvas.dom.overlay);
          }
          /** @type {!Element} */
          var e = document.createElement("br");
          e.setAttribute("clear", "all");
          if (list) {
            list.appendChild(e);
          }
        }
        _canvas.ctx.overlay = _canvas.dom.overlay.getContext("2d");
        _canvas.dom.overlay.width = that.getCanvasSize().x;
        _canvas.dom.overlay.height = that.getCanvasSize().y;
      }
    }
    /**
     * @param {string} json
     * @return {undefined}
     */
    function fn(json) {
      data = json ? json : new exports.a({
        x : that.getWidth(),
        y : that.getHeight()
      });
      /** @type {!Array} */
      query = [_.clone([0, 0]), _.clone([0, data.size.y]), _.clone([data.size.x, data.size.y]), _.clone([data.size.x, 0])];
      self.a.init(data, options.locator);
    }
    /**
     * @return {?}
     */
    function remove() {
      return options.locate ? self.a.locate() : [[_.clone(query[0]), _.clone(query[1]), _.clone(query[2]), _.clone(query[3])]];
    }
    /**
     * @param {!Object} data
     * @return {undefined}
     */
    function draw(data) {
      /**
       * @param {!Array} s
       * @return {undefined}
       */
      function S(s) {
        var l = s.length;
        for (; l--;) {
          s[l][0] += len;
          s[l][1] += d;
        }
      }
      /**
       * @param {!Object} source
       * @return {undefined}
       */
      function add(source) {
        source[0].x += len;
        source[0].y += d;
        source[1].x += len;
        source[1].y += d;
      }
      var i;
      var c = that.getTopRight();
      var len = c.x;
      var d = c.y;
      if (0 !== len || 0 !== d) {
        if (data.barcodes) {
          /** @type {number} */
          i = 0;
          for (; i < data.barcodes.length; i++) {
            draw(data.barcodes[i]);
          }
        }
        if (data.line && 2 === data.line.length && add(data.line), data.box && S(data.box), data.boxes && data.boxes.length > 0) {
          /** @type {number} */
          i = 0;
          for (; i < data.boxes.length; i++) {
            S(data.boxes[i]);
          }
        }
      }
    }
    /**
     * @param {!Function} result
     * @param {!Object} name
     * @return {undefined}
     */
    function log(result, name) {
      if (name && message) {
        if (result.barcodes) {
          result.barcodes.filter(function(result) {
            return result.codeResult;
          }).forEach(function(finaltx) {
            return log(finaltx, name);
          });
        } else {
          if (result.codeResult) {
            message.addResult(name, that.getCanvasSize(), result.codeResult);
          }
        }
      }
    }
    /**
     * @param {!Object} data
     * @return {?}
     */
    function findSelectedData(data) {
      return data && (data.barcodes ? data.barcodes.some(function(result) {
        return result.codeResult;
      }) : data.codeResult);
    }
    /**
     * @param {!Object} data
     * @param {?} callback
     * @return {undefined}
     */
    function render(data, callback) {
      /** @type {!Object} */
      var newData = data;
      if (data && done) {
        draw(data);
        log(data, callback);
        newData = data.barcodes || data;
      }
      node.a.publish("processed", newData);
      if (findSelectedData(data)) {
        node.a.publish("detected", newData);
      }
    }
    /**
     * @return {undefined}
     */
    function append() {
      var output;
      var value;
      value = remove();
      if (value) {
        output = console.decodeFromBoundingBoxes(value);
        output = output || {};
        output.boxes = value;
        render(output, data.data);
      } else {
        render();
      }
    }
    /**
     * @return {undefined}
     */
    function onSuccess() {
      var self;
      if (done) {
        if (a.length > 0) {
          if (!(self = a.filter(function(i) {
            return !i.busy;
          })[0])) {
            return;
          }
          f.attachData(self.imageData);
        } else {
          f.attachData(data.data);
        }
        if (f.grab()) {
          if (self) {
            /** @type {boolean} */
            self.busy = true;
            self.worker.postMessage({
              cmd : "process",
              imageData : self.imageData
            }, [self.imageData.buffer]);
          } else {
            append();
          }
        }
      } else {
        append();
      }
    }
    /**
     * @return {undefined}
     */
    function generate() {
      /** @type {null} */
      var types = null;
      /** @type {number} */
      var tableTypes = 1E3 / (options.frequency || 60);
      /** @type {boolean} */
      T = false;
      (function callback(messageType) {
        types = types || messageType;
        if (!T) {
          if (messageType >= types) {
            types = types + tableTypes;
            onSuccess();
          }
          window.requestAnimFrame(callback);
        }
      })(performance.now());
    }
    /**
     * @return {undefined}
     */
    function parse() {
      if (done && "LiveStream" === options.inputStream.type) {
        generate();
      } else {
        onSuccess();
      }
    }
    /**
     * @param {!Function} fn
     * @return {undefined}
     */
    function update(fn) {
      var src;
      var self = {
        worker : void 0,
        imageData : new Uint8Array(that.getWidth() * that.getHeight()),
        busy : true
      };
      src = getTimer();
      /** @type {!Worker} */
      self.worker = new Worker(src);
      /**
       * @param {!Object} event
       * @return {?}
       */
      self.worker.onmessage = function(event) {
        if ("initialized" === event.data.event) {
          return URL.revokeObjectURL(src), self.busy = false, self.imageData = new Uint8Array(event.data.imageData), fn(self);
        }
        if ("processed" === event.data.event) {
          /** @type {!Uint8Array} */
          self.imageData = new Uint8Array(event.data.imageData);
          /** @type {boolean} */
          self.busy = false;
          render(event.data.result, self.imageData);
        } else {
          event.data.event;
        }
      };
      self.worker.postMessage({
        cmd : "init",
        size : {
          x : that.getWidth(),
          y : that.getHeight()
        },
        imageData : self.imageData,
        config : register(options)
      }, [self.imageData.buffer]);
    }
    /**
     * @param {!Object} options
     * @return {?}
     */
    function register(options) {
      return request({}, options, {
        inputStream : request({}, options.inputStream, {
          target : null
        })
      });
    }
    /**
     * @param {?} post
     * @return {?}
     */
    function run(post) {
      /**
       * @param {!Object} categories
       * @return {undefined}
       */
      function value(categories) {
        self.postMessage({
          event : "processed",
          imageData : data.data,
          result : categories
        }, [data.data.buffer]);
      }
      /**
       * @return {undefined}
       */
      function a() {
        self.postMessage({
          event : "initialized",
          imageData : data.data
        }, [data.data.buffer]);
      }
      if (post) {
        var exports = post().default;
        if (!exports) {
          return void self.postMessage({
            event : "error",
            message : "Quagga could not be created"
          });
        }
      }
      var data;
      /**
       * @param {!Object} event
       * @return {undefined}
       */
      self.onmessage = function(event) {
        if ("init" === event.data.cmd) {
          var file = event.data.config;
          /** @type {number} */
          file.numOfWorkers = 0;
          data = new exports.ImageWrapper({
            x : event.data.size.x,
            y : event.data.size.y
          }, new Uint8Array(event.data.imageData));
          exports.init(file, a, data);
          exports.onProcessed(value);
        } else {
          if ("process" === event.data.cmd) {
            /** @type {!Uint8Array} */
            data.data = new Uint8Array(event.data.imageData);
            exports.start();
          } else {
            if ("setReaders" === event.data.cmd) {
              exports.setReaders(event.data.readers);
            }
          }
        }
      };
    }
    /**
     * @return {?}
     */
    function getTimer() {
      var blob;
      var c;
      return void 0 !== value && (c = value), blob = new Blob(["(" + run.toString() + ")(" + c + ");"], {
        type : "text/javascript"
      }), window.URL.createObjectURL(blob);
    }
    /**
     * @param {!Object} readers
     * @return {undefined}
     */
    function onmessage(readers) {
      if (console) {
        console.setReaders(readers);
      } else {
        if (done && a.length > 0) {
          a.forEach(function(thread) {
            thread.worker.postMessage({
              cmd : "setReaders",
              readers : readers
            });
          });
        }
      }
    }
    /**
     * @param {number} b
     * @param {!Function} f
     * @return {?}
     */
    function map(b, f) {
      /** @type {number} */
      var i = b - a.length;
      if (0 === i) {
        return f && f();
      }
      if (i < 0) {
        return a.slice(i).forEach(function(instance) {
          instance.worker.terminate();
        }), a = a.slice(0, i), f && f();
      }
      /**
       * @param {?} n
       * @return {undefined}
       */
      var build = function(n) {
        a.push(n);
        if (a.length >= b && f) {
          f();
        }
      };
      /** @type {number} */
      var redIndex = 0;
      for (; redIndex < i; redIndex++) {
        update(build);
      }
    }
    Object.defineProperty($scope, "__esModule", {
      value : true
    });
    var that;
    var f;
    var T;
    var data;
    var query;
    var console;
    var message;
    var script = $(28);
    var require = $.n(script);
    var z = $(54);
    var exports = ($.n(z), $(20));
    var self = $(64);
    var k = $(57);
    var node = $(51);
    var subject = $(59);
    var content_panes = $(9);
    var B = $(49);
    var input = $(55);
    var clonedI = $(63);
    var AvatarsIO = $(61);
    /** @type {function(!Object, ...(Object|null)): !Object} */
    var request = Object.assign || function(b) {
      /** @type {number} */
      var i = 1;
      for (; i < arguments.length; i++) {
        var data = arguments[i];
        var k;
        for (k in data) {
          if (Object.prototype.hasOwnProperty.call(data, k)) {
            b[k] = data[k];
          }
        }
      }
      return b;
    };
    var _ = {
      clone : $(7)
    };
    var _canvas = {
      ctx : {
        image : null,
        overlay : null
      },
      dom : {
        image : null,
        overlay : null
      }
    };
    /** @type {!Array} */
    var a = [];
    /** @type {boolean} */
    var done = true;
    var options = {};
    $scope.default = {
      init : function(data, value, err) {
        if (options = require()({}, input.a, data), err) {
          return done = false, cb(err), value();
        }
        build(value);
      },
      start : function() {
        parse();
      },
      stop : function() {
        /** @type {boolean} */
        T = true;
        map(0);
        if ("LiveStream" === options.inputStream.type) {
          subject.a.release();
          that.clearEventHandlers();
        }
      },
      pause : function() {
        /** @type {boolean} */
        T = true;
      },
      onDetected : function(fn) {
        node.a.subscribe("detected", fn);
      },
      offDetected : function(socketWrapper) {
        node.a.unsubscribe("detected", socketWrapper);
      },
      onProcessed : function(type) {
        node.a.subscribe("processed", type);
      },
      offProcessed : function(socketWrapper) {
        node.a.unsubscribe("processed", socketWrapper);
      },
      setReaders : function(readers) {
        onmessage(readers);
      },
      registerResultCollector : function(callback) {
        if (callback && "function" == typeof callback.addResult) {
          /** @type {!Object} */
          message = callback;
        }
      },
      canvas : _canvas,
      decodeSingle : function(file, prev) {
        var boardCtrl = this;
        file = require()({
          inputStream : {
            type : "ImageStream",
            sequence : false,
            size : 800,
            src : file.src
          },
          numOfWorkers : 1,
          locator : {
            halfSample : false
          }
        }, file);
        this.init(file, function() {
          node.a.once("processed", function(a) {
            boardCtrl.stop();
            prev.call(null, a);
          }, true);
          parse();
        });
      },
      ImageWrapper : exports.a,
      ImageDebug : content_panes.a,
      ResultCollector : B.a,
      CameraAccess : subject.a
    };
  }, function(canCreateDiscussions, $rootScope, pointFromEvent) {
    /**
     * @param {string} discoveryItem
     * @param {!Array} array
     * @return {?}
     */
    function contains(discoveryItem, array) {
      return !!array && array.some(function(removalSpecification) {
        return Object.keys(removalSpecification).every(function(removalKey) {
          return removalSpecification[removalKey] === discoveryItem[removalKey];
        });
      });
    }
    /**
     * @param {string} data
     * @param {?} validator
     * @return {?}
     */
    function add(data, validator) {
      return "function" != typeof validator || validator(data);
    }
    var p = pointFromEvent(9);
    $rootScope.a = {
      create : function(options) {
        /**
         * @param {string} item
         * @return {?}
         */
        function add(item) {
          return replaceWith && item && !contains(item, options.blacklist) && add(item, options.filter);
        }
        /** @type {!Element} */
        var clone = document.createElement("canvas");
        var i = clone.getContext("2d");
        /** @type {!Array} */
        var results = [];
        var replaceWith = options.capacity || 20;
        /** @type {boolean} */
        var s = options.capture === true;
        return {
          addResult : function(name, model, title) {
            var result = {};
            if (add(title)) {
              replaceWith--;
              /** @type {string} */
              result.codeResult = title;
              if (s) {
                clone.width = model.x;
                clone.height = model.y;
                p.a.drawImage(name, model, i);
                result.frame = clone.toDataURL();
              }
              results.push(result);
            }
          },
          getResults : function() {
            return results;
          }
        };
      }
    };
  }, function(canCreateDiscussions, $rootScope, require) {
    var vec2 = {
      clone : require(7),
      dot : require(32)
    };
    $rootScope.a = {
      create : function(name, properties) {
        /**
         * @return {undefined}
         */
        function add() {
          o(name);
          updateCenter();
        }
        /**
         * @param {!Object} t
         * @return {undefined}
         */
        function o(t) {
          /** @type {!Object} */
          newState[t.id] = t;
          points.push(t);
        }
        /**
         * @return {undefined}
         */
        function updateCenter() {
          var i;
          /** @type {number} */
          var sum = 0;
          /** @type {number} */
          i = 0;
          for (; i < points.length; i++) {
            sum = sum + points[i].rad;
          }
          /** @type {number} */
          center.rad = sum / points.length;
          center.vec = vec2.clone([Math.cos(center.rad), Math.sin(center.rad)]);
        }
        /** @type {!Array} */
        var points = [];
        var center = {
          rad : 0,
          vec : vec2.clone([0, 0])
        };
        var newState = {};
        return add(), {
          add : function(t) {
            if (!newState[t.id]) {
              o(t);
              updateCenter();
            }
          },
          fits : function(pos) {
            return Math.abs(vec2.dot(pos.point.vec, center.vec)) > properties;
          },
          getPoints : function() {
            return points;
          },
          getCenter : function() {
            return center;
          }
        };
      },
      createPoint : function(newPoint, id, property) {
        return {
          rad : newPoint[property],
          point : newPoint,
          id : id
        };
      }
    };
  }, function(canCreateDiscussions, defaultTagAttributes, n) {
    defaultTagAttributes.a = function() {
      /**
       * @param {string} eventName
       * @return {?}
       */
      function getEvent(eventName) {
        return events[eventName] || (events[eventName] = {
          subscribers : []
        }), events[eventName];
      }
      /**
       * @return {undefined}
       */
      function _filling() {
        events = {};
      }
      /**
       * @param {!Object} data
       * @param {?} obj
       * @return {undefined}
       */
      function callback(data, obj) {
        if (data.async) {
          setTimeout(function() {
            data.callback(obj);
          }, 4);
        } else {
          data.callback(obj);
        }
      }
      /**
       * @param {string} event
       * @param {!Object} callback
       * @param {!Object} async
       * @return {undefined}
       */
      function subscribe(event, callback, async) {
        var subscription;
        if ("function" == typeof callback) {
          subscription = {
            callback : callback,
            async : async
          };
        } else {
          if (subscription = callback, !subscription.callback) {
            throw "Callback was not specified on options";
          }
        }
        getEvent(event).subscribers.push(subscription);
      }
      var events = {};
      return {
        subscribe : function(event, callback, async) {
          return subscribe(event, callback, async);
        },
        publish : function(obj, data) {
          var event = getEvent(obj);
          var subscribers = event.subscribers;
          subscribers.filter(function(tcpSocketHandler) {
            return !!tcpSocketHandler.once;
          }).forEach(function(subscriber) {
            callback(subscriber, data);
          });
          event.subscribers = subscribers.filter(function(tcpSocketHandler) {
            return !tcpSocketHandler.once;
          });
          event.subscribers.forEach(function(subscriber) {
            callback(subscriber, data);
          });
        },
        once : function(name, handler, async) {
          subscribe(name, {
            callback : handler,
            async : async,
            once : true
          });
        },
        unsubscribe : function(obj, handler) {
          var event;
          if (obj) {
            event = getEvent(obj);
            event.subscribers = event && handler ? event.subscribers.filter(function(delegate) {
              return delegate.callback !== handler;
            }) : [];
          } else {
            _filling();
          }
        }
      };
    }();
  }, function(canCreateDiscussions, rgbaObj, n) {
    /**
     * @return {?}
     */
    function enumerateDevices() {
      return navigator.mediaDevices && "function" == typeof navigator.mediaDevices.enumerateDevices ? navigator.mediaDevices.enumerateDevices() : Promise.reject(new Error("enumerateDevices is not defined"));
    }
    /**
     * @param {?} constraints
     * @return {?}
     */
    function getUserMedia(constraints) {
      return navigator.mediaDevices && "function" == typeof navigator.mediaDevices.getUserMedia ? navigator.mediaDevices.getUserMedia(constraints) : Promise.reject(new Error("getUserMedia is not defined"));
    }
    /** @type {function(): ?} */
    rgbaObj.b = enumerateDevices;
    /** @type {function(?): ?} */
    rgbaObj.a = getUserMedia;
  }, function(canCreateDiscussions, defaultTagAttributes, n) {
    /**
     * @param {number} from
     * @param {number} size
     * @param {!Object} I
     * @return {undefined}
     */
    function SubImage(from, size, I) {
      if (!I) {
        I = {
          data : null,
          size : size
        };
      }
      /** @type {null} */
      this.data = I.data;
      this.originalSize = I.size;
      /** @type {!Object} */
      this.I = I;
      /** @type {number} */
      this.from = from;
      /** @type {number} */
      this.size = size;
    }
    /**
     * @param {!HTMLCanvasElement} canvas
     * @param {number} scale
     * @return {undefined}
     */
    SubImage.prototype.show = function(canvas, scale) {
      var ctx;
      var data;
      var file;
      var val;
      var y;
      var x;
      var cameraXCell;
      if (!scale) {
        /** @type {number} */
        scale = 1;
      }
      ctx = canvas.getContext("2d");
      canvas.width = this.size.x;
      canvas.height = this.size.y;
      data = ctx.getImageData(0, 0, canvas.width, canvas.height);
      file = data.data;
      /** @type {number} */
      val = 0;
      /** @type {number} */
      y = 0;
      for (; y < this.size.y; y++) {
        /** @type {number} */
        x = 0;
        for (; x < this.size.x; x++) {
          /** @type {number} */
          cameraXCell = y * this.size.x + x;
          /** @type {number} */
          val = this.get(x, y) * scale;
          /** @type {number} */
          file[4 * cameraXCell + 0] = val;
          /** @type {number} */
          file[4 * cameraXCell + 1] = val;
          /** @type {number} */
          file[4 * cameraXCell + 2] = val;
          /** @type {number} */
          file[4 * cameraXCell + 3] = 255;
        }
      }
      data.data = file;
      ctx.putImageData(data, 0, 0);
    };
    /**
     * @param {!Object} index
     * @param {!Object} y
     * @return {?}
     */
    SubImage.prototype.get = function(index, y) {
      return this.data[(this.from.y + y) * this.originalSize.x + this.from.x + index];
    };
    /**
     * @param {!Object} data
     * @return {undefined}
     */
    SubImage.prototype.updateData = function(data) {
      this.originalSize = data.size;
      this.data = data.data;
    };
    /**
     * @param {number} from
     * @return {?}
     */
    SubImage.prototype.updateFrom = function(from) {
      return this.from = from, this;
    };
    /** @type {function(number, number, !Object): undefined} */
    defaultTagAttributes.a = SubImage;
  }, function(canCreateDiscussions, isSlidingUp) {
    if ("undefined" != typeof window) {
      window.requestAnimFrame = function() {
        return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function(rafFunction) {
          window.setTimeout(rafFunction, 1E3 / 60);
        };
      }();
    }
    /** @type {function(number, number): number} */
    Math.imul = Math.imul || function(resizeParams, moveOut) {
      /** @type {number} */
      var ox = resizeParams >>> 16 & 65535;
      /** @type {number} */
      var oy = 65535 & resizeParams;
      /** @type {number} */
      var c = moveOut >>> 16 & 65535;
      /** @type {number} */
      var b = 65535 & moveOut;
      return oy * b + (ox * b + oy * c << 16 >>> 0) | 0;
    };
    if ("function" != typeof Object.assign) {
      /**
       * @param {!Object} target
       * @param {...(Object|null)} p1
       * @return {!Object}
       */
      Object.assign = function(target) {
        if (null === target) {
          throw new TypeError("Cannot convert undefined or null to object");
        }
        /** @type {!Object} */
        var output = Object(target);
        /** @type {number} */
        var i = 1;
        for (; i < arguments.length; i++) {
          var source = arguments[i];
          if (null !== source) {
            var name;
            for (name in source) {
              if (Object.prototype.hasOwnProperty.call(source, name)) {
                output[name] = source[name];
              }
            }
          }
        }
        return output;
      };
    }
  }, function(canCreateDiscussions, p, Clazz_doubleToInt) {
    var n = void 0;
    n = Clazz_doubleToInt(56);
    p.a = n;
  }, function(mixin, canCreateDiscussions) {
    mixin.exports = {
      inputStream : {
        name : "Live",
        type : "LiveStream",
        constraints : {
          width : 640,
          height : 480,
          facingMode : "environment"
        },
        area : {
          top : "0%",
          right : "0%",
          left : "0%",
          bottom : "0%"
        },
        singleChannel : false
      },
      locate : true,
      numOfWorkers : 4,
      decoder : {
        readers : ["code_128_reader"]
      },
      locator : {
        halfSample : true,
        patchSize : "medium"
      }
    };
  }, function(canCreateDiscussions, $rootScope, require) {
    var c = require(58);
    var content_panes = (require(9), require(69));
    var i = require(4);
    var a = require(31);
    var u = require(70);
    var clonedI = require(68);
    var defaultTagAttributes = require(77);
    var f = require(74);
    var super$0 = require(72);
    var d = require(73);
    var h = require(76);
    var p = require(75);
    var v = require(67);
    var _ = require(71);
    /** @type {function(!Object): ?} */
    var log = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(aNetChannelMessage) {
      return typeof aNetChannelMessage;
    } : function(obj) {
      return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
    var READERS = {
      code_128_reader : content_panes.a,
      ean_reader : i.a,
      ean_5_reader : d.a,
      ean_2_reader : super$0.a,
      ean_8_reader : f.a,
      code_39_reader : a.a,
      code_39_vin_reader : u.a,
      codabar_reader : clonedI.a,
      upc_reader : defaultTagAttributes.a,
      upc_e_reader : h.a,
      i2of5_reader : p.a,
      "2of5_reader" : v.a,
      code_93_reader : _.a
    };
    $rootScope.a = {
      create : function(config, inputImageWrapper) {
        /**
         * @return {undefined}
         */
        function checkForMapLink() {
        }
        /**
         * @return {undefined}
         */
        function initReaders() {
          config.readers.forEach(function(data) {
            var reader;
            var configuration = {};
            /** @type {!Array} */
            var supplements = [];
            if ("object" === (void 0 === data ? "undefined" : log(data))) {
              reader = data.format;
              configuration = data.config;
            } else {
              if ("string" == typeof data) {
                /** @type {!Object} */
                reader = data;
              }
            }
            if (configuration.supplements) {
              supplements = configuration.supplements.map(function(supplement) {
                return new READERS[supplement];
              });
            }
            _barcodeReaders.push(new READERS[reader](configuration, supplements));
          });
        }
        /**
         * @return {undefined}
         */
        function _addFilterEvents() {
        }
        /**
         * @param {!Object} line
         * @param {!Object} angle
         * @param {number} ext
         * @return {?}
         */
        function getExtendedLine(line, angle, ext) {
          /**
           * @param {number} amount
           * @return {undefined}
           */
          function extendLine(amount) {
            var box = {
              y : amount * Math.sin(angle),
              x : amount * Math.cos(angle)
            };
            line[0].y -= box.y;
            line[0].x -= box.x;
            line[1].y += box.y;
            line[1].x += box.x;
          }
          extendLine(ext);
          for (; ext > 1 && (!inputImageWrapper.inImageWithBorder(line[0], 0) || !inputImageWrapper.inImageWithBorder(line[1], 0));) {
            /** @type {number} */
            ext = ext - Math.ceil(ext / 2);
            extendLine(-ext);
          }
          return line;
        }
        /**
         * @param {!Array} box
         * @return {?}
         */
        function getLine(box) {
          return [{
            x : (box[1][0] - box[0][0]) / 2 + box[0][0],
            y : (box[1][1] - box[0][1]) / 2 + box[0][1]
          }, {
            x : (box[3][0] - box[2][0]) / 2 + box[2][0],
            y : (box[3][1] - box[2][1]) / 2 + box[2][1]
          }];
        }
        /**
         * @param {!Object} line
         * @return {?}
         */
        function tryDecode(line) {
          var i;
          /** @type {null} */
          var result = null;
          var barcodeLine = c.a.getBarcodeLine(inputImageWrapper, line[0], line[1]);
          c.a.toBinaryLine(barcodeLine);
          /** @type {number} */
          i = 0;
          for (; i < _barcodeReaders.length && null === result; i++) {
            result = _barcodeReaders[i].decodePattern(barcodeLine.line);
          }
          return null === result ? null : {
            codeResult : result,
            barcodeLine : barcodeLine
          };
        }
        /**
         * @param {!Object} box
         * @param {!Object} line
         * @param {!Object} lineAngle
         * @return {?}
         */
        function tryDecodeBruteForce(box, line, lineAngle) {
          var r;
          var dir;
          var obj;
          /** @type {number} */
          var hValue = Math.sqrt(Math.pow(box[1][0] - box[0][0], 2) + Math.pow(box[1][1] - box[0][1], 2));
          /** @type {number} */
          var max = 16;
          /** @type {null} */
          var result = null;
          /** @type {number} */
          var xdir = Math.sin(lineAngle);
          /** @type {number} */
          var ydir = Math.cos(lineAngle);
          /** @type {number} */
          r = 1;
          for (; r < max && null === result; r++) {
            /** @type {number} */
            dir = hValue / max * r * (r % 2 == 0 ? -1 : 1);
            obj = {
              y : dir * xdir,
              x : dir * ydir
            };
            line[0].y += obj.x;
            line[0].x -= obj.y;
            line[1].y += obj.x;
            line[1].x -= obj.y;
            result = tryDecode(line);
          }
          return result;
        }
        /**
         * @param {!Object} line
         * @return {?}
         */
        function getLineLength(line) {
          return Math.sqrt(Math.pow(Math.abs(line[1].y - line[0].y), 2) + Math.pow(Math.abs(line[1].x - line[0].x), 2));
        }
        /**
         * @param {!Array} box
         * @return {?}
         */
        function decodeFromBoundingBox(box) {
          var line;
          var lineAngle;
          var result;
          var max;
          _canvas.ctx.overlay;
          return line = getLine(box), max = getLineLength(line), lineAngle = Math.atan2(line[1].y - line[0].y, line[1].x - line[0].x), null === (line = getExtendedLine(line, lineAngle, Math.floor(.1 * max))) ? null : (result = tryDecode(line), null === result && (result = tryDecodeBruteForce(box, line, lineAngle)), null === result ? null : {
            codeResult : result.codeResult,
            line : line,
            angle : lineAngle,
            pattern : result.barcodeLine.line,
            threshold : result.barcodeLine.threshold
          });
        }
        var _canvas = {
          ctx : {
            frequency : null,
            pattern : null,
            overlay : null
          },
          dom : {
            frequency : null,
            pattern : null,
            overlay : null
          }
        };
        /** @type {!Array} */
        var _barcodeReaders = [];
        return checkForMapLink(), initReaders(), _addFilterEvents(), {
          decodeFromBoundingBox : function(box) {
            return decodeFromBoundingBox(box);
          },
          decodeFromBoundingBoxes : function(boxes) {
            var i;
            var result;
            /** @type {!Array} */
            var barcodes = [];
            var multiple = config.multiple;
            /** @type {number} */
            i = 0;
            for (; i < boxes.length; i++) {
              var box = boxes[i];
              if (result = decodeFromBoundingBox(box) || {}, result.box = box, multiple) {
                barcodes.push(result);
              } else {
                if (result.codeResult) {
                  return result;
                }
              }
            }
            if (multiple) {
              return {
                barcodes : barcodes
              };
            }
          },
          setReaders : function(readers) {
            /** @type {!Object} */
            config.readers = readers;
            /** @type {number} */
            _barcodeReaders.length = 0;
            initReaders();
          }
        };
      }
    };
  }, function(canCreateDiscussions, defaultTagAttributes, saveNotifs) {
    var Bresenham = (saveNotifs(20), {});
    var Slope = {
      DIR : {
        UP : 1,
        DOWN : -1
      }
    };
    /**
     * @param {!Object} imageWrapper
     * @param {!Object} p1
     * @param {!Object} p2
     * @return {?}
     */
    Bresenham.getBarcodeLine = function(imageWrapper, p1, p2) {
      /**
       * @param {number} y
       * @param {number} x
       * @return {undefined}
       */
      function read(y, x) {
        ret = data[x * scale + y];
        i = i + ret;
        min = ret < min ? ret : min;
        max = ret > max ? ret : max;
        found.push(ret);
      }
      var ArticleMatchBonus;
      var CommentMatchPenalty;
      var regExBonusMultiplier;
      var theTokenLength;
      var index;
      var m;
      var x;
      var ret;
      /** @type {number} */
      var l = 0 | p1.x;
      /** @type {number} */
      var t = 0 | p1.y;
      /** @type {number} */
      var r = 0 | p2.x;
      /** @type {number} */
      var d = 0 | p2.y;
      /** @type {boolean} */
      var _ = Math.abs(d - t) > Math.abs(r - l);
      /** @type {!Array} */
      var found = [];
      var data = imageWrapper.data;
      var scale = imageWrapper.size.x;
      /** @type {number} */
      var i = 0;
      /** @type {number} */
      var min = 255;
      /** @type {number} */
      var max = 0;
      if (_) {
        /** @type {number} */
        m = l;
        /** @type {number} */
        l = t;
        /** @type {number} */
        t = m;
        /** @type {number} */
        m = r;
        /** @type {number} */
        r = d;
        /** @type {number} */
        d = m;
      }
      if (l > r) {
        /** @type {number} */
        m = l;
        /** @type {number} */
        l = r;
        /** @type {number} */
        r = m;
        /** @type {number} */
        m = t;
        /** @type {number} */
        t = d;
        /** @type {number} */
        d = m;
      }
      /** @type {number} */
      ArticleMatchBonus = r - l;
      /** @type {number} */
      CommentMatchPenalty = Math.abs(d - t);
      /** @type {number} */
      regExBonusMultiplier = ArticleMatchBonus / 2 | 0;
      /** @type {number} */
      index = t;
      /** @type {number} */
      theTokenLength = t < d ? 1 : -1;
      /** @type {number} */
      x = l;
      for (; x < r; x++) {
        if (_) {
          read(index, x);
        } else {
          read(x, index);
        }
        if ((regExBonusMultiplier = regExBonusMultiplier - CommentMatchPenalty) < 0) {
          /** @type {number} */
          index = index + theTokenLength;
          /** @type {number} */
          regExBonusMultiplier = regExBonusMultiplier + ArticleMatchBonus;
        }
      }
      return {
        line : found,
        min : min,
        max : max
      };
    };
    /**
     * @param {!Object} result
     * @return {?}
     */
    Bresenham.toBinaryLine = function(result) {
      var e;
      var On;
      var lastTrackTitle;
      var track;
      var i;
      var j;
      var min = result.min;
      var max = result.max;
      var line = result.line;
      var center = min + (max - min) / 2;
      /** @type {!Array} */
      var stops = [];
      /** @type {number} */
      var threshold = (max - min) / 12;
      /** @type {number} */
      var thresholdNeg = -threshold;
      /** @type {number} */
      lastTrackTitle = line[0] > center ? Slope.DIR.UP : Slope.DIR.DOWN;
      stops.push({
        pos : 0,
        val : line[0]
      });
      /** @type {number} */
      i = 0;
      for (; i < line.length - 2; i++) {
        /** @type {number} */
        e = line[i + 1] - line[i];
        /** @type {number} */
        On = line[i + 2] - line[i + 1];
        /** @type {number} */
        track = e + On < thresholdNeg && line[i + 1] < 1.5 * center ? Slope.DIR.DOWN : e + On > threshold && line[i + 1] > .5 * center ? Slope.DIR.UP : lastTrackTitle;
        if (lastTrackTitle !== track) {
          stops.push({
            pos : i,
            val : line[i]
          });
          /** @type {number} */
          lastTrackTitle = track;
        }
      }
      stops.push({
        pos : line.length,
        val : line[line.length - 1]
      });
      j = stops[0].pos;
      for (; j < stops[1].pos; j++) {
        /** @type {number} */
        line[j] = line[j] > center ? 0 : 1;
      }
      /** @type {number} */
      i = 1;
      for (; i < stops.length - 1; i++) {
        /** @type {number} */
        threshold = stops[i + 1].val > stops[i].val ? stops[i].val + (stops[i + 1].val - stops[i].val) / 3 * 2 | 0 : stops[i + 1].val + (stops[i].val - stops[i + 1].val) / 3 | 0;
        j = stops[i].pos;
        for (; j < stops[i + 1].pos; j++) {
          /** @type {number} */
          line[j] = line[j] > threshold ? 0 : 1;
        }
      }
      return {
        line : line,
        threshold : threshold
      };
    };
    Bresenham.debug = {
      printFrequency : function(line, canvas) {
        var i;
        var ctx = canvas.getContext("2d");
        canvas.width = line.length;
        /** @type {number} */
        canvas.height = 256;
        ctx.beginPath();
        /** @type {string} */
        ctx.strokeStyle = "blue";
        /** @type {number} */
        i = 0;
        for (; i < line.length; i++) {
          ctx.moveTo(i, 255);
          ctx.lineTo(i, 255 - line[i]);
        }
        ctx.stroke();
        ctx.closePath();
      },
      printPattern : function(line, canvas) {
        var i;
        var ctx = canvas.getContext("2d");
        canvas.width = line.length;
        /** @type {string} */
        ctx.fillColor = "black";
        /** @type {number} */
        i = 0;
        for (; i < line.length; i++) {
          if (1 === line[i]) {
            ctx.fillRect(i, 0, 1, 100);
          }
        }
      }
    };
    defaultTagAttributes.a = Bresenham;
  }, function(canCreateDiscussions, result, test) {
    /**
     * @param {(HTMLVideoElement|Image)} video
     * @return {?}
     */
    function exports(video) {
      return new Promise(function(eachcb, nowDone) {
        /**
         * @return {undefined}
         */
        function done() {
          if (o > 0) {
            if (video.videoWidth > 10 && video.videoHeight > 10) {
              eachcb();
            } else {
              window.setTimeout(done, 500);
            }
          } else {
            nowDone("Unable to play video stream. Is webcam working?");
          }
          o--;
        }
        /** @type {number} */
        var o = 10;
        done();
      });
    }
    /**
     * @param {!Element} video
     * @param {?} width
     * @return {?}
     */
    function initCamera(video, width) {
      return test.i(t.a)(width).then(function(stream) {
        return new Promise(function(saveNotifs) {
          /** @type {!MediaStream} */
          mediaStream = stream;
          video.setAttribute("autoplay", true);
          video.setAttribute("muted", true);
          video.setAttribute("playsinline", true);
          /** @type {!MediaStream} */
          video.srcObject = stream;
          video.addEventListener("loadedmetadata", function() {
            video.play();
            saveNotifs();
          });
        });
      }).then(exports.bind(null, video));
    }
    /**
     * @param {?} videoConstraints
     * @return {?}
     */
    function deprecatedConstraints(videoConstraints) {
      var normalized = pick()(videoConstraints, ["width", "height", "facingMode", "aspectRatio", "deviceId"]);
      return void 0 !== videoConstraints.minAspectRatio && videoConstraints.minAspectRatio > 0 && (normalized.aspectRatio = videoConstraints.minAspectRatio, console.log("WARNING: Constraint 'minAspectRatio' is deprecated; Use 'aspectRatio' instead")), void 0 !== videoConstraints.facing && (normalized.facingMode = videoConstraints.facing, console.log("WARNING: Constraint 'facing' is deprecated. Use 'facingMode' instead'")), normalized;
    }
    /**
     * @param {?} videoConstraints
     * @return {?}
     */
    function pickConstraints(videoConstraints) {
      var options = {
        audio : false,
        video : deprecatedConstraints(videoConstraints)
      };
      return options.video.deviceId && options.video.facingMode && delete options.video.facingMode, Promise.resolve(options);
    }
    /**
     * @return {?}
     */
    function enumerateVideoDevices() {
      return test.i(t.b)().then(function(swimlanes) {
        return swimlanes.filter(function(mobValue) {
          return "videoinput" === mobValue.kind;
        });
      });
    }
    /**
     * @return {?}
     */
    function getActiveTrack() {
      if (mediaStream) {
        var blockHostsStr = mediaStream.getVideoTracks();
        if (blockHostsStr && blockHostsStr.length) {
          return blockHostsStr[0];
        }
      }
    }
    var mediaStream;
    var options = test(162);
    var pick = test.n(options);
    var t = test(52);
    result.a = {
      request : function(video, videoConstraints) {
        return pickConstraints(videoConstraints).then(initCamera.bind(null, video));
      },
      release : function() {
        var targets = mediaStream && mediaStream.getVideoTracks();
        if (targets && targets.length) {
          targets[0].stop();
        }
        /** @type {null} */
        mediaStream = null;
      },
      enumerateVideoDevices : enumerateVideoDevices,
      getActiveStreamLabel : function() {
        var prev = getActiveTrack();
        return prev ? prev.label : "";
      },
      getActiveTrack : getActiveTrack
    };
  }, function(canCreateDiscussions, fn, n) {
    /**
     * @param {?} url
     * @return {?}
     */
    function load(url) {
      var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : bod;
      return /^blob:/i.test(url) ? fetch(url).then(readToBuffer).then(function(buffer) {
        return findTagsInBuffer(buffer, e);
      }) : Promise.resolve(null);
    }
    /**
     * @param {?} blob
     * @return {?}
     */
    function readToBuffer(blob) {
      return new Promise(function(cb) {
        /** @type {!FileReader} */
        var reader = new FileReader;
        /**
         * @param {!Event} fileLoadedEvent
         * @return {?}
         */
        reader.onload = function(fileLoadedEvent) {
          return cb(fileLoadedEvent.target.result);
        };
        reader.readAsArrayBuffer(blob);
      });
    }
    /**
     * @param {?} url
     * @return {?}
     */
    function fetch(url) {
      return new Promise(function(onLoad, resolve) {
        /** @type {!XMLHttpRequest} */
        var xhr = new XMLHttpRequest;
        xhr.open("GET", url, true);
        /** @type {string} */
        xhr.responseType = "blob";
        /**
         * @return {undefined}
         */
        xhr.onreadystatechange = function() {
          if (!(xhr.readyState !== XMLHttpRequest.DONE || 200 !== xhr.status && 0 !== xhr.status)) {
            onLoad(this.response);
          }
        };
        xhr.onerror = resolve;
        xhr.send();
      });
    }
    /**
     * @param {(Uint8Array|string)} file
     * @return {?}
     */
    function findTagsInBuffer(file) {
      var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : bod;
      /** @type {!DataView} */
      var dataView = new DataView(file);
      var fileLength = file.byteLength;
      var exifTags = e.reduce(function(r, x) {
        /** @type {string} */
        var n = Object.keys(l).filter(function(offset) {
          return l[offset] === x;
        })[0];
        return n && (r[n] = x), r;
      }, {});
      /** @type {number} */
      var offset = 2;
      if (255 !== dataView.getUint8(0) || 216 !== dataView.getUint8(1)) {
        return false;
      }
      for (; offset < fileLength;) {
        if (255 !== dataView.getUint8(offset)) {
          return false;
        }
        if (225 === dataView.getUint8(offset + 1)) {
          return readEXIFData(dataView, offset + 4, exifTags);
        }
        /** @type {number} */
        offset = offset + (2 + dataView.getUint16(offset + 2));
      }
    }
    /**
     * @param {!DataView} file
     * @param {number} start
     * @param {?} exifTags
     * @return {?}
     */
    function readEXIFData(file, start, exifTags) {
      if ("Exif" !== getStringFromDB(file, start, 4)) {
        return false;
      }
      var tiffOffset = start + 6;
      var bigEnd = void 0;
      if (18761 === file.getUint16(tiffOffset)) {
        /** @type {boolean} */
        bigEnd = false;
      } else {
        if (19789 !== file.getUint16(tiffOffset)) {
          return false;
        }
        /** @type {boolean} */
        bigEnd = true;
      }
      if (42 !== file.getUint16(tiffOffset + 2, !bigEnd)) {
        return false;
      }
      var firstIFDOffset = file.getUint32(tiffOffset + 4, !bigEnd);
      return !(firstIFDOffset < 8) && readTags(file, tiffOffset, tiffOffset + firstIFDOffset, exifTags, bigEnd);
    }
    /**
     * @param {!DataView} file
     * @param {?} tiffStart
     * @param {number} dirStart
     * @param {?} strings
     * @param {!Object} bigEnd
     * @return {?}
     */
    function readTags(file, tiffStart, dirStart, strings, bigEnd) {
      var entries = file.getUint16(dirStart, !bigEnd);
      var tags = {};
      /** @type {number} */
      var i = 0;
      for (; i < entries; i++) {
        var entryOffset = dirStart + 12 * i + 2;
        var tag = strings[file.getUint16(entryOffset, !bigEnd)];
        if (tag) {
          tags[tag] = readTagValue(file, entryOffset, tiffStart, dirStart, bigEnd);
        }
      }
      return tags;
    }
    /**
     * @param {!DataView} file
     * @param {number} entryOffset
     * @param {?} tiffStart
     * @param {number} dirStart
     * @param {!Object} bigEnd
     * @return {?}
     */
    function readTagValue(file, entryOffset, tiffStart, dirStart, bigEnd) {
      var i = file.getUint16(entryOffset + 2, !bigEnd);
      var a = file.getUint32(entryOffset + 4, !bigEnd);
      switch(i) {
        case 3:
          if (1 === a) {
            return file.getUint16(entryOffset + 8, !bigEnd);
          }
      }
    }
    /**
     * @param {!DataView} buffer
     * @param {number} start
     * @param {number} length
     * @return {?}
     */
    function getStringFromDB(buffer, start, length) {
      /** @type {string} */
      var outstr = "";
      /** @type {number} */
      var n = start;
      for (; n < start + length; n++) {
        /** @type {string} */
        outstr = outstr + String.fromCharCode(buffer.getUint8(n));
      }
      return outstr;
    }
    /** @type {function(?): ?} */
    fn.a = load;
    var l = {
      274 : "orientation"
    };
    /** @type {!Array<?>} */
    var bod = Object.keys(l).map(function(eventName) {
      return l[eventName];
    });
  }, function(canCreateDiscussions, handler, test) {
    /**
     * @param {!Object} a
     * @param {!Object} b
     * @return {undefined}
     */
    function extend(a, b) {
      if (a.width !== b.x) {
        a.width = b.x;
      }
      if (a.height !== b.y) {
        a.height = b.y;
      }
    }
    var t = test(19);
    /** @type {number} */
    var rad = Math.PI / 180;
    var fn = {};
    /**
     * @param {!Object} that
     * @param {string} data
     * @return {?}
     */
    fn.create = function(that, data) {
      var response;
      var _that = {};
      var _streamConfig = that.getConfig();
      var params = (test.i(t.b)(that.getRealWidth(), that.getRealHeight()), that.getCanvasSize());
      var size = test.i(t.b)(that.getWidth(), that.getHeight());
      var bbox = that.getTopRight();
      var x = bbox.x;
      var cy = bbox.y;
      /** @type {null} */
      var ctx = null;
      /** @type {null} */
      var _data = null;
      return response = data ? data : document.createElement("canvas"), response.width = params.x, response.height = params.y, ctx = response.getContext("2d"), _data = new Uint8Array(size.x * size.y), _that.attachData = function(data) {
        /** @type {!Object} */
        _data = data;
      }, _that.getData = function() {
        return _data;
      }, _that.grab = function() {
        var parentSelector;
        var frameIsWindow = _streamConfig.halfSample;
        var options = that.getFrame();
        var name = options;
        /** @type {number} */
        var angle = 0;
        if (name) {
          if (extend(response, params), "ImageStream" === _streamConfig.type && (name = options.img, options.tags && options.tags.orientation)) {
            switch(options.tags.orientation) {
              case 6:
                /** @type {number} */
                angle = 90 * rad;
                break;
              case 8:
                /** @type {number} */
                angle = -90 * rad;
            }
          }
          return 0 !== angle ? (ctx.translate(params.x / 2, params.y / 2), ctx.rotate(angle), ctx.drawImage(name, -params.y / 2, -params.x / 2, params.y, params.x), ctx.rotate(-angle), ctx.translate(-params.x / 2, -params.y / 2)) : ctx.drawImage(name, 0, 0, params.x, params.y), parentSelector = ctx.getImageData(x, cy, size.x, size.y).data, frameIsWindow ? test.i(t.c)(parentSelector, size, _data) : test.i(t.d)(parentSelector, _data, _streamConfig), true;
        }
        return false;
      }, _that.getSize = function() {
        return size;
      }, _that;
    };
    handler.a = fn;
  }, function(canCreateDiscussions, $rootScope, test) {
    /**
     * @param {!Node} img
     * @param {!Object} htmlImagesArray
     * @return {undefined}
     */
    function addOnloadHandler(img, htmlImagesArray) {
      /**
       * @return {undefined}
       */
      img.onload = function() {
        htmlImagesArray.loaded(this);
      };
    }
    var t = test(60);
    var prop = {};
    /**
     * @param {string} directory
     * @param {!Function} callback
     * @param {number} offset
     * @param {number} name
     * @param {(Object|boolean)} vertexShaderFile
     * @return {undefined}
     */
    prop.load = function(directory, callback, offset, name, vertexShaderFile) {
      var i;
      var img;
      var index;
      /** @type {!Array} */
      var htmlImagesSrcArray = new Array(name);
      /** @type {!Array} */
      var htmlImagesArray = new Array(htmlImagesSrcArray.length);
      if (vertexShaderFile === false) {
        /** @type {string} */
        htmlImagesSrcArray[0] = directory;
      } else {
        /** @type {number} */
        i = 0;
        for (; i < htmlImagesSrcArray.length; i++) {
          index = offset + i;
          /** @type {string} */
          htmlImagesSrcArray[i] = directory + "image-" + ("00" + index).slice(-3) + ".jpg";
        }
      }
      /** @type {!Array} */
      htmlImagesArray.notLoaded = [];
      /**
       * @param {!Object} img
       * @return {undefined}
       */
      htmlImagesArray.addImage = function(img) {
        htmlImagesArray.notLoaded.push(img);
      };
      /**
       * @param {string} loadedImg
       * @return {undefined}
       */
      htmlImagesArray.loaded = function(loadedImg) {
        var notloadedImgs = htmlImagesArray.notLoaded;
        /** @type {number} */
        var x = 0;
        for (; x < notloadedImgs.length; x++) {
          if (notloadedImgs[x] === loadedImg) {
            notloadedImgs.splice(x, 1);
            /** @type {number} */
            var y = 0;
            for (; y < htmlImagesSrcArray.length; y++) {
              var packedStringDelimiter = htmlImagesSrcArray[y].substr(htmlImagesSrcArray[y].lastIndexOf("/"));
              if (loadedImg.src.lastIndexOf(packedStringDelimiter) !== -1) {
                htmlImagesArray[y] = {
                  img : loadedImg
                };
                break;
              }
            }
            break;
          }
        }
        if (0 === notloadedImgs.length) {
          if (vertexShaderFile === false) {
            test.i(t.a)(directory, ["orientation"]).then(function(tag) {
              /** @type {!Object} */
              htmlImagesArray[0].tags = tag;
              callback(htmlImagesArray);
            }).catch(function(animate_param) {
              console.log(animate_param);
              callback(htmlImagesArray);
            });
          } else {
            callback(htmlImagesArray);
          }
        }
      };
      /** @type {number} */
      i = 0;
      for (; i < htmlImagesSrcArray.length; i++) {
        /** @type {!Image} */
        img = new Image;
        htmlImagesArray.addImage(img);
        addOnloadHandler(img, htmlImagesArray);
        img.src = htmlImagesSrcArray[i];
      }
    };
    $rootScope.a = prop;
  }, function(canCreateDiscussions, module, new_val_func) {
    var h = new_val_func(62);
    var InputStream = {};
    /**
     * @param {!Object} video
     * @return {?}
     */
    InputStream.createVideoStream = function(video) {
      /**
       * @return {undefined}
       */
      function initSize() {
        var width = video.videoWidth;
        var height = video.videoHeight;
        calculatedWidth = _config.size ? width / height > 1 ? _config.size : Math.floor(width / height * _config.size) : width;
        calculatedHeight = _config.size ? width / height > 1 ? Math.floor(height / width * _config.size) : _config.size : height;
        _canvasSize.x = calculatedWidth;
        _canvasSize.y = calculatedHeight;
      }
      var calculatedWidth;
      var calculatedHeight;
      var that = {};
      /** @type {null} */
      var _config = null;
      /** @type {!Array} */
      var _eventNames = ["canrecord", "ended"];
      var eventListeners = {};
      var _topRight = {
        x : 0,
        y : 0
      };
      var _canvasSize = {
        x : 0,
        y : 0
      };
      return that.getRealWidth = function() {
        return video.videoWidth;
      }, that.getRealHeight = function() {
        return video.videoHeight;
      }, that.getWidth = function() {
        return calculatedWidth;
      }, that.getHeight = function() {
        return calculatedHeight;
      }, that.setWidth = function(width) {
        /** @type {number} */
        calculatedWidth = width;
      }, that.setHeight = function(height) {
        /** @type {number} */
        calculatedHeight = height;
      }, that.setInputStream = function(config) {
        /** @type {!Object} */
        _config = config;
        video.src = void 0 !== config.src ? config.src : "";
      }, that.ended = function() {
        return video.ended;
      }, that.getConfig = function() {
        return _config;
      }, that.setAttribute = function(name, value) {
        video.setAttribute(name, value);
      }, that.pause = function() {
        video.pause();
      }, that.play = function() {
        video.play();
      }, that.setCurrentTime = function(value) {
        if ("LiveStream" !== _config.type) {
          /** @type {number} */
          video.currentTime = value;
        }
      }, that.addEventListener = function(event, callback, useCapture) {
        if (_eventNames.indexOf(event) !== -1) {
          if (!eventListeners[event]) {
            /** @type {!Array} */
            eventListeners[event] = [];
          }
          eventListeners[event].push(callback);
        } else {
          video.addEventListener(event, callback, useCapture);
        }
      }, that.clearEventHandlers = function() {
        _eventNames.forEach(function(type) {
          var listeners = eventListeners[type];
          if (listeners && listeners.length > 0) {
            listeners.forEach(function(f) {
              video.removeEventListener(type, f);
            });
          }
        });
      }, that.trigger = function(eventName, data) {
        var i;
        var listeners = eventListeners[eventName];
        if ("canrecord" === eventName && initSize(), listeners && listeners.length > 0) {
          /** @type {number} */
          i = 0;
          for (; i < listeners.length; i++) {
            listeners[i].apply(that, data);
          }
        }
      }, that.setTopRight = function(topRight) {
        _topRight.x = topRight.x;
        _topRight.y = topRight.y;
      }, that.getTopRight = function() {
        return _topRight;
      }, that.setCanvasSize = function(size) {
        _canvasSize.x = size.x;
        _canvasSize.y = size.y;
      }, that.getCanvasSize = function() {
        return _canvasSize;
      }, that.getFrame = function() {
        return video;
      }, that;
    };
    /**
     * @param {!Object} video
     * @return {?}
     */
    InputStream.createLiveStream = function(video) {
      video.setAttribute("autoplay", true);
      var currentVideo = InputStream.createVideoStream(video);
      return currentVideo.ended = function() {
        return false;
      }, currentVideo;
    };
    /**
     * @return {?}
     */
    InputStream.createImageStream = function() {
      /**
       * @return {undefined}
       */
      function loadImages() {
        /** @type {boolean} */
        noaccum = false;
        h.a.load(baseUrl, function(posts) {
          if (results = posts, posts[0].tags && posts[0].tags.orientation) {
            switch(posts[0].tags.orientation) {
              case 6:
              case 8:
                width = posts[0].img.height;
                height = posts[0].img.width;
                break;
              default:
                width = posts[0].img.width;
                height = posts[0].img.height;
            }
          } else {
            width = posts[0].img.width;
            height = posts[0].img.height;
          }
          calculatedWidth = _config.size ? width / height > 1 ? _config.size : Math.floor(width / height * _config.size) : width;
          calculatedHeight = _config.size ? width / height > 1 ? Math.floor(height / width * _config.size) : _config.size : height;
          _canvasSize.x = calculatedWidth;
          _canvasSize.y = calculatedHeight;
          /** @type {boolean} */
          noaccum = true;
          /** @type {number} */
          i = 0;
          setTimeout(function() {
            publishEvent("canrecord", []);
          }, 0);
        }, p, steps, _config.sequence);
      }
      /**
       * @param {string} eventName
       * @param {!Array} data
       * @return {undefined}
       */
      function publishEvent(eventName, data) {
        var i;
        var listeners = eventListeners[eventName];
        if (listeners && listeners.length > 0) {
          /** @type {number} */
          i = 0;
          for (; i < listeners.length; i++) {
            listeners[i].apply(that, data);
          }
        }
      }
      var calculatedWidth;
      var calculatedHeight;
      var that = {};
      /** @type {null} */
      var _config = null;
      /** @type {number} */
      var width = 0;
      /** @type {number} */
      var height = 0;
      /** @type {number} */
      var i = 0;
      /** @type {boolean} */
      var outputFn = true;
      /** @type {boolean} */
      var noaccum = false;
      /** @type {null} */
      var results = null;
      /** @type {number} */
      var steps = 0;
      /** @type {number} */
      var p = 1;
      /** @type {null} */
      var baseUrl = null;
      /** @type {boolean} */
      var ended = false;
      /** @type {!Array} */
      var _eventNames = ["canrecord", "ended"];
      var eventListeners = {};
      var _topRight = {
        x : 0,
        y : 0
      };
      var _canvasSize = {
        x : 0,
        y : 0
      };
      return that.trigger = publishEvent, that.getWidth = function() {
        return calculatedWidth;
      }, that.getHeight = function() {
        return calculatedHeight;
      }, that.setWidth = function(width) {
        /** @type {number} */
        calculatedWidth = width;
      }, that.setHeight = function(height) {
        /** @type {number} */
        calculatedHeight = height;
      }, that.getRealWidth = function() {
        return width;
      }, that.getRealHeight = function() {
        return height;
      }, that.setInputStream = function(config) {
        /** @type {!Object} */
        _config = config;
        if (config.sequence === false) {
          baseUrl = config.src;
          /** @type {number} */
          steps = 1;
        } else {
          baseUrl = config.src;
          steps = config.length;
        }
        loadImages();
      }, that.ended = function() {
        return ended;
      }, that.setAttribute = function() {
      }, that.getConfig = function() {
        return _config;
      }, that.pause = function() {
        /** @type {boolean} */
        outputFn = true;
      }, that.play = function() {
        /** @type {boolean} */
        outputFn = false;
      }, that.setCurrentTime = function(position) {
        /** @type {number} */
        i = position;
      }, that.addEventListener = function(event, callback) {
        if (_eventNames.indexOf(event) !== -1) {
          if (!eventListeners[event]) {
            /** @type {!Array} */
            eventListeners[event] = [];
          }
          eventListeners[event].push(callback);
        }
      }, that.setTopRight = function(topRight) {
        _topRight.x = topRight.x;
        _topRight.y = topRight.y;
      }, that.getTopRight = function() {
        return _topRight;
      }, that.setCanvasSize = function(size) {
        _canvasSize.x = size.x;
        _canvasSize.y = size.y;
      }, that.getCanvasSize = function() {
        return _canvasSize;
      }, that.getFrame = function() {
        var result;
        return noaccum ? (outputFn || (result = results[i], i < steps - 1 ? i++ : setTimeout(function() {
          /** @type {boolean} */
          ended = true;
          publishEvent("ended", []);
        }, 0)), result) : null;
      }, that;
    };
    module.a = InputStream;
  }, function(canCreateDiscussions, e, $) {
    (function(value) {
      /**
       * @return {undefined}
       */
      function initBuffers() {
        var skeletonImageData;
        unit = _config.halfSample ? new exports.a({
          x : i.size.x / 2 | 0,
          y : i.size.y / 2 | 0
        }) : i;
        size = $.i(t.e)(_config.patchSize, unit.size);
        /** @type {number} */
        _numPatches.x = unit.size.x / size.x | 0;
        /** @type {number} */
        _numPatches.y = unit.size.y / size.y | 0;
        _binaryImageWrapper = new exports.a(unit.size, void 0, Uint8Array, false);
        data = new exports.a(size, void 0, Array, true);
        /** @type {!ArrayBuffer} */
        skeletonImageData = new ArrayBuffer(65536);
        _subImageWrapper = new exports.a(size, new Uint8Array(skeletonImageData, 0, size.x * size.y));
        a = new exports.a(size, new Uint8Array(skeletonImageData, size.x * size.y * 3, size.x * size.y), void 0, true);
        _skeletonizer = $.i(account.a)("undefined" != typeof window ? window : "undefined" != typeof self ? self : value, {
          size : size.x
        }, skeletonImageData);
        _imageToPatchGrid = new exports.a({
          x : unit.size.x / _subImageWrapper.size.x | 0,
          y : unit.size.y / _subImageWrapper.size.y | 0
        }, void 0, Array, true);
        _patchGrid = new exports.a(_imageToPatchGrid.size, void 0, void 0, true);
        _patchLabelGrid = new exports.a(_imageToPatchGrid.size, void 0, Int32Array, true);
      }
      /**
       * @return {undefined}
       */
      function initCanvas() {
        if (!(_config.useWorker || "undefined" == typeof document)) {
          /** @type {!Element} */
          _canvasContainer.dom.binary = document.createElement("canvas");
          /** @type {string} */
          _canvasContainer.dom.binary.className = "binaryBuffer";
          _canvasContainer.ctx.binary = _canvasContainer.dom.binary.getContext("2d");
          _canvasContainer.dom.binary.width = _binaryImageWrapper.size.x;
          _canvasContainer.dom.binary.height = _binaryImageWrapper.size.y;
        }
      }
      /**
       * @param {!Array} patches
       * @return {?}
       */
      function boxFromPatches(patches) {
        var theta;
        var i;
        var j;
        var patch;
        var matrix;
        var box;
        var origin;
        var tA2x = _binaryImageWrapper.size.x;
        var nodeTly = _binaryImageWrapper.size.y;
        /** @type {number} */
        var f = -_binaryImageWrapper.size.x;
        /** @type {number} */
        var l = -_binaryImageWrapper.size.y;
        /** @type {number} */
        theta = 0;
        /** @type {number} */
        i = 0;
        for (; i < patches.length; i++) {
          patch = patches[i];
          theta = theta + patch.rad;
        }
        /** @type {number} */
        theta = theta / patches.length;
        /** @type {number} */
        theta = (180 * theta / Math.PI + 90) % 180 - 90;
        if (theta < 0) {
          /** @type {number} */
          theta = theta + 180;
        }
        /** @type {number} */
        theta = (180 - theta) * Math.PI / 180;
        matrix = self.copy(self.create(), [Math.cos(theta), Math.sin(theta), -Math.sin(theta), Math.cos(theta)]);
        /** @type {number} */
        i = 0;
        for (; i < patches.length; i++) {
          patch = patches[i];
          /** @type {number} */
          j = 0;
          for (; j < 4; j++) {
            vec2.transformMat2(patch.box[j], patch.box[j], matrix);
          }
        }
        /** @type {number} */
        i = 0;
        for (; i < patches.length; i++) {
          patch = patches[i];
          /** @type {number} */
          j = 0;
          for (; j < 4; j++) {
            if (patch.box[j][0] < tA2x) {
              tA2x = patch.box[j][0];
            }
            if (patch.box[j][0] > f) {
              f = patch.box[j][0];
            }
            if (patch.box[j][1] < nodeTly) {
              nodeTly = patch.box[j][1];
            }
            if (patch.box[j][1] > l) {
              l = patch.box[j][1];
            }
          }
        }
        /** @type {!Array} */
        box = [[tA2x, nodeTly], [f, nodeTly], [f, l], [tA2x, l]];
        /** @type {number} */
        origin = _config.halfSample ? 2 : 1;
        matrix = self.invert(matrix, matrix);
        /** @type {number} */
        j = 0;
        for (; j < 4; j++) {
          vec2.transformMat2(box[j], box[j], matrix);
        }
        /** @type {number} */
        j = 0;
        for (; j < 4; j++) {
          vec2.scale(box[j], box[j], origin);
        }
        return box;
      }
      /**
       * @return {undefined}
       */
      function binarizeImage() {
        $.i(t.f)(unit, _binaryImageWrapper);
        _binaryImageWrapper.zeroBorder();
      }
      /**
       * @return {?}
       */
      function findPatches() {
        var i;
        var j;
        var x;
        var y;
        var moments;
        var options;
        var rasterResult;
        /** @type {!Array} */
        var patchesFound = [];
        /** @type {number} */
        i = 0;
        for (; i < _numPatches.x; i++) {
          /** @type {number} */
          j = 0;
          for (; j < _numPatches.y; j++) {
            /** @type {number} */
            x = _subImageWrapper.size.x * i;
            /** @type {number} */
            y = _subImageWrapper.size.y * j;
            skeletonize(x, y);
            a.zeroBorder();
            T.a.init(data.data, 0);
            options = S.a.create(a, data);
            rasterResult = options.rasterize(0);
            moments = data.moments(rasterResult.count);
            /** @type {!Array<?>} */
            patchesFound = patchesFound.concat(describePatch(moments, [i, j], x, y));
          }
        }
        return patchesFound;
      }
      /**
       * @param {number} maxLabel
       * @return {?}
       */
      function findBiggestConnectedAreas(maxLabel) {
        var i;
        var j;
        /** @type {!Array} */
        var r = [];
        /** @type {number} */
        i = 0;
        for (; i < maxLabel; i++) {
          r.push(0);
        }
        j = _patchLabelGrid.data.length;
        for (; j--;) {
          if (_patchLabelGrid.data[j] > 0) {
            r[_patchLabelGrid.data[j] - 1]++;
          }
        }
        return r = r.map(function(insertStr1, size) {
          return {
            val : insertStr1,
            label : size + 1
          };
        }), r.sort(function(b, a) {
          return a.val - b.val;
        }), r.filter(function(putter_11187) {
          return putter_11187.val >= 5;
        });
      }
      /**
       * @param {!Object} topLabels
       * @param {?} maxLabel
       * @return {?}
       */
      function findBoxes(topLabels, maxLabel) {
        var i;
        var j;
        var line;
        var box;
        /** @type {!Array} */
        var patches = [];
        /** @type {!Array} */
        var boxes = [];
        /** @type {number} */
        i = 0;
        for (; i < topLabels.length; i++) {
          j = _patchLabelGrid.data.length;
          /** @type {number} */
          patches.length = 0;
          for (; j--;) {
            if (_patchLabelGrid.data[j] === topLabels[i].label) {
              line = _imageToPatchGrid.data[j];
              patches.push(line);
            }
          }
          box = boxFromPatches(patches);
          if (box) {
            boxes.push(box);
          }
        }
        return boxes;
      }
      /**
       * @param {!Array} moments
       * @return {?}
       */
      function similarMoments(moments) {
        var dmin = $.i(t.g)(moments, .9);
        var bottomPanels = $.i(t.h)(dmin, 1, function(p_spline) {
          return p_spline.getPoints().length;
        });
        /** @type {!Array} */
        var wstones = [];
        /** @type {!Array} */
        var result = [];
        if (1 === bottomPanels.length) {
          wstones = bottomPanels[0].item.getPoints();
          /** @type {number} */
          var i = 0;
          for (; i < wstones.length; i++) {
            result.push(wstones[i].point);
          }
        }
        return result;
      }
      /**
       * @param {!Object} x
       * @param {!Object} y
       * @return {undefined}
       */
      function skeletonize(x, y) {
        _binaryImageWrapper.subImageAsCopy(_subImageWrapper, $.i(t.b)(x, y));
        _skeletonizer.skeletonize();
      }
      /**
       * @param {!Object} moments
       * @param {!Object} patchPos
       * @param {number} x
       * @param {number} y
       * @return {?}
       */
      function describePatch(moments, patchPos, x, y) {
        var k;
        var avg;
        var matchingMoments;
        var patch;
        /** @type {!Array} */
        var eligibleMoments = [];
        /** @type {!Array} */
        var patchesFound = [];
        /** @type {number} */
        var minComponentWeight = Math.ceil(size.x / 3);
        if (moments.length >= 2) {
          /** @type {number} */
          k = 0;
          for (; k < moments.length; k++) {
            if (moments[k].m00 > minComponentWeight) {
              eligibleMoments.push(moments[k]);
            }
          }
          if (eligibleMoments.length >= 2) {
            matchingMoments = similarMoments(eligibleMoments);
            /** @type {number} */
            avg = 0;
            /** @type {number} */
            k = 0;
            for (; k < matchingMoments.length; k++) {
              avg = avg + matchingMoments[k].rad;
            }
            if (matchingMoments.length > 1 && matchingMoments.length >= eligibleMoments.length / 4 * 3 && matchingMoments.length > moments.length / 4) {
              /** @type {number} */
              avg = avg / matchingMoments.length;
              patch = {
                index : patchPos[1] * _numPatches.x + patchPos[0],
                pos : {
                  x : x,
                  y : y
                },
                box : [vec2.clone([x, y]), vec2.clone([x + _subImageWrapper.size.x, y]), vec2.clone([x + _subImageWrapper.size.x, y + _subImageWrapper.size.y]), vec2.clone([x, y + _subImageWrapper.size.y])],
                moments : matchingMoments,
                rad : avg,
                vec : vec2.clone([Math.cos(avg), Math.sin(avg)])
              };
              patchesFound.push(patch);
            }
          }
        }
        return patchesFound;
      }
      /**
       * @param {!Object} patchesFound
       * @return {?}
       */
      function rasterizeAngularSimilarity(patchesFound) {
        /**
         * @return {?}
         */
        function notYetProcessed() {
          var i;
          /** @type {number} */
          i = 0;
          for (; i < _patchLabelGrid.data.length; i++) {
            if (0 === _patchLabelGrid.data[i] && 1 === _patchGrid.data[i]) {
              return i;
            }
          }
          return _patchLabelGrid.length;
        }
        /**
         * @param {number} currentIdx
         * @return {undefined}
         */
        function trace(currentIdx) {
          var lineYAtZero;
          var lineSlope;
          var currentPatch;
          var idx;
          var dir;
          var leftRenderRect = {
            x : currentIdx % _patchLabelGrid.size.x,
            y : currentIdx / _patchLabelGrid.size.x | 0
          };
          if (currentIdx < _patchLabelGrid.data.length) {
            currentPatch = _imageToPatchGrid.data[currentIdx];
            _patchLabelGrid.data[currentIdx] = label;
            /** @type {number} */
            dir = 0;
            for (; dir < D.a.searchDirections.length; dir++) {
              lineSlope = leftRenderRect.y + D.a.searchDirections[dir][0];
              lineYAtZero = leftRenderRect.x + D.a.searchDirections[dir][1];
              idx = lineSlope * _patchLabelGrid.size.x + lineYAtZero;
              if (0 !== _patchGrid.data[idx]) {
                if (0 === _patchLabelGrid.data[idx] && Math.abs(vec2.dot(_imageToPatchGrid.data[idx].vec, currentPatch.vec)) > a) {
                  trace(idx);
                }
              } else {
                /** @type {number} */
                _patchLabelGrid.data[idx] = Number.MAX_VALUE;
              }
            }
          }
        }
        var i;
        var patch;
        /** @type {number} */
        var label = 0;
        /** @type {number} */
        var a = .95;
        /** @type {number} */
        var currIdx = 0;
        T.a.init(_patchGrid.data, 0);
        T.a.init(_patchLabelGrid.data, 0);
        T.a.init(_imageToPatchGrid.data, null);
        /** @type {number} */
        i = 0;
        for (; i < patchesFound.length; i++) {
          patch = patchesFound[i];
          _imageToPatchGrid.data[patch.index] = patch;
          /** @type {number} */
          _patchGrid.data[patch.index] = 1;
        }
        _patchGrid.zeroBorder();
        for (; (currIdx = notYetProcessed()) < _patchLabelGrid.data.length;) {
          label++;
          trace(currIdx);
        }
        return label;
      }
      var _config;
      var unit;
      var a;
      var _subImageWrapper;
      var data;
      var _patchGrid;
      var _patchLabelGrid;
      var _imageToPatchGrid;
      var _binaryImageWrapper;
      var size;
      var i;
      var _skeletonizer;
      var exports = $(20);
      var t = $(19);
      var T = $(3);
      var S = ($(9), $(65));
      var D = $(30);
      var account = $(66);
      var vec2 = {
        clone : $(7),
        dot : $(32),
        scale : $(81),
        transformMat2 : $(82)
      };
      var self = {
        copy : $(78),
        create : $(79),
        invert : $(80)
      };
      var _canvasContainer = {
        ctx : {
          binary : null
        },
        dom : {
          binary : null
        }
      };
      var _numPatches = {
        x : 0,
        y : 0
      };
      e.a = {
        init : function(value, obj) {
          /** @type {!Function} */
          _config = obj;
          i = value;
          initBuffers();
          initCanvas();
        },
        locate : function() {
          var patchesFound;
          var topLabels;
          if (_config.halfSample && $.i(t.i)(i, unit), binarizeImage(), patchesFound = findPatches(), patchesFound.length < _numPatches.x * _numPatches.y * .05) {
            return null;
          }
          var maxLabel = rasterizeAngularSimilarity(patchesFound);
          return maxLabel < 1 ? null : (topLabels = findBiggestConnectedAreas(maxLabel), 0 === topLabels.length ? null : findBoxes(topLabels, maxLabel));
        },
        checkImageConstraints : function(inputStream, config) {
          var oldScale;
          var point;
          var area;
          var width = inputStream.getWidth();
          var height = inputStream.getHeight();
          /** @type {number} */
          var halfSample = config.halfSample ? .5 : 1;
          if (inputStream.getConfig().area && (area = $.i(t.j)(width, height, inputStream.getConfig().area), inputStream.setTopRight({
            x : area.sx,
            y : area.sy
          }), inputStream.setCanvasSize({
            x : width,
            y : height
          }), width = area.sw, height = area.sh), point = {
            x : Math.floor(width * halfSample),
            y : Math.floor(height * halfSample)
          }, oldScale = $.i(t.e)(config.patchSize, point), inputStream.setWidth(Math.floor(Math.floor(point.x / oldScale.x) * (1 / halfSample) * oldScale.x)), inputStream.setHeight(Math.floor(Math.floor(point.y / oldScale.y) * (1 / halfSample) * oldScale.y)), inputStream.getWidth() % oldScale.x == 0 && inputStream.getHeight() % oldScale.y == 0) {
            return true;
          }
          throw new Error("Image dimensions do not comply with the current settings: Width (" + width + " )and height (" + height + ") must a multiple of " + oldScale.x);
        }
      };
    }).call(e, $(47));
  }, function(canCreateDiscussions, defaultTagAttributes, unitToColor) {
    var c = unitToColor(30);
    var Rasterizer = {
      createContour2D : function() {
        return {
          dir : null,
          index : null,
          firstVertex : null,
          insideContours : null,
          nextpeer : null,
          prevpeer : null
        };
      },
      CONTOUR_DIR : {
        CW_DIR : 0,
        CCW_DIR : 1,
        UNKNOWN_DIR : 2
      },
      DIR : {
        OUTSIDE_EDGE : -32767,
        INSIDE_EDGE : -32766
      },
      create : function(options, properties) {
        var imageData = options.data;
        var labelData = properties.data;
        var width = options.size.x;
        var height = options.size.y;
        var tracer = c.a.create(options, properties);
        return {
          rasterize : function(depthlabel) {
            var color;
            var bc;
            var lc;
            var labelindex;
            var cx;
            var cy;
            var vertex;
            var p;
            var cc;
            var sc;
            var pos;
            var i;
            /** @type {!Array} */
            var colorMap = [];
            /** @type {number} */
            var connectedCount = 0;
            /** @type {number} */
            i = 0;
            for (; i < 400; i++) {
              /** @type {number} */
              colorMap[i] = 0;
            }
            colorMap[0] = imageData[0];
            /** @type {null} */
            cc = null;
            /** @type {number} */
            cy = 1;
            for (; cy < height - 1; cy++) {
              /** @type {number} */
              labelindex = 0;
              bc = colorMap[0];
              /** @type {number} */
              cx = 1;
              for (; cx < width - 1; cx++) {
                if (pos = cy * width + cx, 0 === labelData[pos]) {
                  if ((color = imageData[pos]) !== bc) {
                    if (0 === labelindex) {
                      /** @type {number} */
                      lc = connectedCount + 1;
                      colorMap[lc] = color;
                      bc = color;
                      if (null !== (vertex = tracer.contourTracing(cy, cx, lc, color, Rasterizer.DIR.OUTSIDE_EDGE))) {
                        connectedCount++;
                        /** @type {number} */
                        labelindex = lc;
                        p = Rasterizer.createContour2D();
                        /** @type {number} */
                        p.dir = Rasterizer.CONTOUR_DIR.CW_DIR;
                        /** @type {number} */
                        p.index = labelindex;
                        p.firstVertex = vertex;
                        p.nextpeer = cc;
                        /** @type {null} */
                        p.insideContours = null;
                        if (null !== cc) {
                          cc.prevpeer = p;
                        }
                        cc = p;
                      }
                    } else {
                      if (null !== (vertex = tracer.contourTracing(cy, cx, Rasterizer.DIR.INSIDE_EDGE, color, labelindex))) {
                        p = Rasterizer.createContour2D();
                        p.firstVertex = vertex;
                        /** @type {null} */
                        p.insideContours = null;
                        /** @type {number} */
                        p.dir = 0 === depthlabel ? Rasterizer.CONTOUR_DIR.CCW_DIR : Rasterizer.CONTOUR_DIR.CW_DIR;
                        /** @type {number} */
                        p.index = depthlabel;
                        sc = cc;
                        for (; null !== sc && sc.index !== labelindex;) {
                          sc = sc.nextpeer;
                        }
                        if (null !== sc) {
                          p.nextpeer = sc.insideContours;
                          if (null !== sc.insideContours) {
                            sc.insideContours.prevpeer = p;
                          }
                          sc.insideContours = p;
                        }
                      }
                    }
                  } else {
                    /** @type {number} */
                    labelData[pos] = labelindex;
                  }
                } else {
                  if (labelData[pos] === Rasterizer.DIR.OUTSIDE_EDGE || labelData[pos] === Rasterizer.DIR.INSIDE_EDGE) {
                    /** @type {number} */
                    labelindex = 0;
                    bc = labelData[pos] === Rasterizer.DIR.INSIDE_EDGE ? imageData[pos] : colorMap[0];
                  } else {
                    labelindex = labelData[pos];
                    bc = colorMap[labelindex];
                  }
                }
              }
            }
            sc = cc;
            for (; null !== sc;) {
              /** @type {number} */
              sc.index = depthlabel;
              sc = sc.nextpeer;
            }
            return {
              cc : cc,
              count : connectedCount
            };
          },
          debug : {
            drawContour : function(ctx, cont) {
              var iq;
              var q;
              var p;
              var g = ctx.getContext("2d");
              /** @type {!Object} */
              var pq = cont;
              /** @type {string} */
              g.strokeStyle = "red";
              /** @type {string} */
              g.fillStyle = "red";
              /** @type {number} */
              g.lineWidth = 1;
              iq = null !== pq ? pq.insideContours : null;
              for (; null !== pq;) {
                switch(null !== iq ? (q = iq, iq = iq.nextpeer) : (q = pq, pq = pq.nextpeer, iq = null !== pq ? pq.insideContours : null), q.dir) {
                  case Rasterizer.CONTOUR_DIR.CW_DIR:
                    /** @type {string} */
                    g.strokeStyle = "red";
                    break;
                  case Rasterizer.CONTOUR_DIR.CCW_DIR:
                    /** @type {string} */
                    g.strokeStyle = "blue";
                    break;
                  case Rasterizer.CONTOUR_DIR.UNKNOWN_DIR:
                    /** @type {string} */
                    g.strokeStyle = "green";
                }
                p = q.firstVertex;
                g.beginPath();
                g.moveTo(p.x, p.y);
                do {
                  p = p.next;
                  g.lineTo(p.x, p.y);
                } while (p !== q.firstVertex);
                g.stroke();
              }
            }
          }
        };
      }
    };
    defaultTagAttributes.a = Rasterizer;
  }, function(canCreateDiscussions, emsElems, __webpack_require__) {
    /**
     * @param {!Window} stdlib
     * @param {?} foreign
     * @param {?} buffer
     * @return {?}
     */
    function Skeletonizer(stdlib, foreign, buffer) {
      /**
       * @param {number} inImagePtr
       * @param {number} outImagePtr
       * @return {undefined}
       */
      function erode(inImagePtr, outImagePtr) {
        /** @type {number} */
        inImagePtr = inImagePtr | 0;
        /** @type {number} */
        outImagePtr = outImagePtr | 0;
        /** @type {number} */
        var v = 0;
        /** @type {number} */
        var u = 0;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        var yStart1 = 0;
        /** @type {number} */
        var yStart2 = 0;
        /** @type {number} */
        var xStart1 = 0;
        /** @type {number} */
        var xStart2 = 0;
        /** @type {number} */
        var offset = 0;
        /** @type {number} */
        v = 1;
        for (; (v | 0) < (size - 1 | 0); v = v + 1 | 0) {
          /** @type {number} */
          offset = offset + size | 0;
          /** @type {number} */
          u = 1;
          for (; (u | 0) < (size - 1 | 0); u = u + 1 | 0) {
            /** @type {number} */
            yStart1 = offset - size | 0;
            /** @type {number} */
            yStart2 = offset + size | 0;
            /** @type {number} */
            xStart1 = u - 1 | 0;
            /** @type {number} */
            xStart2 = u + 1 | 0;
            /** @type {number} */
            sum = (HEAPU16[inImagePtr + yStart1 + xStart1 | 0] | 0) + (HEAPU16[inImagePtr + yStart1 + xStart2 | 0] | 0) + (HEAPU16[inImagePtr + offset + u | 0] | 0) + (HEAPU16[inImagePtr + yStart2 + xStart1 | 0] | 0) + (HEAPU16[inImagePtr + yStart2 + xStart2 | 0] | 0) | 0;
            if ((sum | 0) == (5 | 0)) {
              /** @type {number} */
              HEAPU16[outImagePtr + offset + u | 0] = 1;
            } else {
              /** @type {number} */
              HEAPU16[outImagePtr + offset + u | 0] = 0;
            }
          }
        }
        return;
      }
      /**
       * @param {number} aImagePtr
       * @param {number} bImagePtr
       * @param {number} outImagePtr
       * @return {undefined}
       */
      function subtract(aImagePtr, bImagePtr, outImagePtr) {
        /** @type {number} */
        aImagePtr = aImagePtr | 0;
        /** @type {number} */
        bImagePtr = bImagePtr | 0;
        /** @type {number} */
        outImagePtr = outImagePtr | 0;
        /** @type {number} */
        var length = 0;
        /** @type {number} */
        length = imul(size, size) | 0;
        for (; (length | 0) > 0;) {
          /** @type {number} */
          length = length - 1 | 0;
          /** @type {number} */
          HEAPU16[outImagePtr + length | 0] = (HEAPU16[aImagePtr + length | 0] | 0) - (HEAPU16[bImagePtr + length | 0] | 0) | 0;
        }
      }
      /**
       * @param {number} aImagePtr
       * @param {number} bImagePtr
       * @param {number} outImagePtr
       * @return {undefined}
       */
      function bitwiseOr(aImagePtr, bImagePtr, outImagePtr) {
        /** @type {number} */
        aImagePtr = aImagePtr | 0;
        /** @type {number} */
        bImagePtr = bImagePtr | 0;
        /** @type {number} */
        outImagePtr = outImagePtr | 0;
        /** @type {number} */
        var length = 0;
        /** @type {number} */
        length = imul(size, size) | 0;
        for (; (length | 0) > 0;) {
          /** @type {number} */
          length = length - 1 | 0;
          /** @type {number} */
          HEAPU16[outImagePtr + length | 0] = HEAPU16[aImagePtr + length | 0] | 0 | (HEAPU16[bImagePtr + length | 0] | 0) | 0;
        }
      }
      /**
       * @param {number} imagePtr
       * @return {?}
       */
      function countNonZero(imagePtr) {
        /** @type {number} */
        imagePtr = imagePtr | 0;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        var y = 0;
        /** @type {number} */
        y = imul(size, size) | 0;
        for (; (y | 0) > 0;) {
          /** @type {number} */
          y = y - 1 | 0;
          /** @type {number} */
          sum = (sum | 0) + (HEAPU16[imagePtr + y | 0] | 0) | 0;
        }
        return sum | 0;
      }
      /**
       * @param {number} imagePtr
       * @param {number} value
       * @return {undefined}
       */
      function init(imagePtr, value) {
        /** @type {number} */
        imagePtr = imagePtr | 0;
        /** @type {number} */
        value = value | 0;
        /** @type {number} */
        var y = 0;
        /** @type {number} */
        y = imul(size, size) | 0;
        for (; (y | 0) > 0;) {
          /** @type {number} */
          y = y - 1 | 0;
          /** @type {number} */
          HEAPU16[imagePtr + y | 0] = value;
        }
      }
      /**
       * @param {number} inImagePtr
       * @param {number} outImagePtr
       * @return {undefined}
       */
      function dilate(inImagePtr, outImagePtr) {
        /** @type {number} */
        inImagePtr = inImagePtr | 0;
        /** @type {number} */
        outImagePtr = outImagePtr | 0;
        /** @type {number} */
        var v = 0;
        /** @type {number} */
        var u = 0;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        var yStart1 = 0;
        /** @type {number} */
        var yStart2 = 0;
        /** @type {number} */
        var xStart1 = 0;
        /** @type {number} */
        var xStart2 = 0;
        /** @type {number} */
        var offset = 0;
        /** @type {number} */
        v = 1;
        for (; (v | 0) < (size - 1 | 0); v = v + 1 | 0) {
          /** @type {number} */
          offset = offset + size | 0;
          /** @type {number} */
          u = 1;
          for (; (u | 0) < (size - 1 | 0); u = u + 1 | 0) {
            /** @type {number} */
            yStart1 = offset - size | 0;
            /** @type {number} */
            yStart2 = offset + size | 0;
            /** @type {number} */
            xStart1 = u - 1 | 0;
            /** @type {number} */
            xStart2 = u + 1 | 0;
            /** @type {number} */
            sum = (HEAPU16[inImagePtr + yStart1 + xStart1 | 0] | 0) + (HEAPU16[inImagePtr + yStart1 + xStart2 | 0] | 0) + (HEAPU16[inImagePtr + offset + u | 0] | 0) + (HEAPU16[inImagePtr + yStart2 + xStart1 | 0] | 0) + (HEAPU16[inImagePtr + yStart2 + xStart2 | 0] | 0) | 0;
            if ((sum | 0) > (0 | 0)) {
              /** @type {number} */
              HEAPU16[outImagePtr + offset + u | 0] = 1;
            } else {
              /** @type {number} */
              HEAPU16[outImagePtr + offset + u | 0] = 0;
            }
          }
        }
        return;
      }
      /**
       * @param {number} srcImagePtr
       * @param {number} dstImagePtr
       * @return {undefined}
       */
      function memcpy(srcImagePtr, dstImagePtr) {
        /** @type {number} */
        srcImagePtr = srcImagePtr | 0;
        /** @type {number} */
        dstImagePtr = dstImagePtr | 0;
        /** @type {number} */
        var length = 0;
        /** @type {number} */
        length = imul(size, size) | 0;
        for (; (length | 0) > 0;) {
          /** @type {number} */
          length = length - 1 | 0;
          /** @type {number} */
          HEAPU16[dstImagePtr + length | 0] = HEAPU16[srcImagePtr + length | 0] | 0;
        }
      }
      /**
       * @param {number} imagePtr
       * @return {undefined}
       */
      function zeroBorder(imagePtr) {
        /** @type {number} */
        imagePtr = imagePtr | 0;
        /** @type {number} */
        var x = 0;
        /** @type {number} */
        var y = 0;
        /** @type {number} */
        x = 0;
        for (; (x | 0) < (size - 1 | 0); x = x + 1 | 0) {
          /** @type {number} */
          HEAPU16[imagePtr + x | 0] = 0;
          /** @type {number} */
          HEAPU16[imagePtr + y | 0] = 0;
          /** @type {number} */
          y = y + size - 1 | 0;
          /** @type {number} */
          HEAPU16[imagePtr + y | 0] = 0;
          /** @type {number} */
          y = y + 1 | 0;
        }
        /** @type {number} */
        x = 0;
        for (; (x | 0) < (size | 0); x = x + 1 | 0) {
          /** @type {number} */
          HEAPU16[imagePtr + y | 0] = 0;
          /** @type {number} */
          y = y + 1 | 0;
        }
      }
      /**
       * @return {undefined}
       */
      function skeletonize() {
        /** @type {number} */
        var subImagePtr = 0;
        /** @type {number} */
        var erodedImagePtr = 0;
        /** @type {number} */
        var tempImagePtr = 0;
        /** @type {number} */
        var skelImagePtr = 0;
        /** @type {number} */
        var sum = 0;
        /** @type {number} */
        var done = 0;
        /** @type {number} */
        erodedImagePtr = imul(size, size) | 0;
        /** @type {number} */
        tempImagePtr = erodedImagePtr + erodedImagePtr | 0;
        /** @type {number} */
        skelImagePtr = tempImagePtr + erodedImagePtr | 0;
        init(skelImagePtr, 0);
        zeroBorder(subImagePtr);
        do {
          erode(subImagePtr, erodedImagePtr);
          dilate(erodedImagePtr, tempImagePtr);
          subtract(subImagePtr, tempImagePtr, tempImagePtr);
          bitwiseOr(skelImagePtr, tempImagePtr, skelImagePtr);
          memcpy(erodedImagePtr, subImagePtr);
          /** @type {number} */
          sum = countNonZero(subImagePtr) | 0;
          /** @type {number} */
          done = (sum | 0) == 0 | 0;
        } while (!done);
      }
      "use asm";
      var HEAPU16 = new stdlib.Uint8Array(buffer);
      /** @type {number} */
      var size = foreign.size | 0;
      var imul = stdlib.Math.imul;
      return {
        skeletonize : skeletonize
      };
    }
    /** @type {function(!Window, ?, ?): ?} */
    emsElems["a"] = Skeletonizer;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @param {?} opts
     * @return {undefined}
     */
    function I2of5Reader(opts) {
      o.a.call(this, opts);
      /** @type {!Array} */
      this.barSpaceRatio = [1, 1];
    }
    var o = keyGen(1);
    /** @type {number} */
    var N = 1;
    /** @type {number} */
    var W = 3;
    var properties = {
      START_PATTERN : {
        value : [W, N, W, N, N, N]
      },
      STOP_PATTERN : {
        value : [W, N, N, N, W]
      },
      CODE_PATTERN : {
        value : [[N, N, W, W, N], [W, N, N, N, W], [N, W, N, N, W], [W, W, N, N, N], [N, N, W, N, W], [W, N, W, N, N], [N, W, W, N, N], [N, N, N, W, W], [W, N, N, W, N], [N, W, N, W, N]]
      },
      SINGLE_CODE_ERROR : {
        value : .78,
        writable : true
      },
      AVG_CODE_ERROR : {
        value : .3,
        writable : true
      },
      FORMAT : {
        value : "2of5"
      }
    };
    var DyMilli = properties.START_PATTERN.value.reduce(function(buckets, name) {
      return buckets + name;
    }, 0);
    /** @type {!Object} */
    I2of5Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?): undefined} */
    I2of5Reader.prototype.constructor = I2of5Reader;
    /**
     * @param {!Object} pattern
     * @param {number} offset
     * @param {boolean} isWhite
     * @param {string} tryHarder
     * @return {?}
     */
    I2of5Reader.prototype._findPattern = function(pattern, offset, isWhite, tryHarder) {
      var i;
      var error;
      var j;
      var sum;
      /** @type {!Array} */
      var counter = [];
      var self = this;
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0
      };
      var epsilon = self.AVG_CODE_ERROR;
      isWhite = isWhite || false;
      tryHarder = tryHarder || false;
      if (!offset) {
        offset = self._nextSet(self._row);
      }
      /** @type {number} */
      i = 0;
      for (; i < pattern.length; i++) {
        /** @type {number} */
        counter[i] = 0;
      }
      /** @type {number} */
      i = offset;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            /** @type {number} */
            sum = 0;
            /** @type {number} */
            j = 0;
            for (; j < counter.length; j++) {
              sum = sum + counter[j];
            }
            if ((error = self._matchPattern(counter, pattern)) < epsilon) {
              return bestMatch.error = error, bestMatch.start = i - sum, bestMatch.end = i, bestMatch;
            }
            if (!tryHarder) {
              return null;
            }
            /** @type {number} */
            j = 0;
            for (; j < counter.length - 2; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[counter.length - 2] = 0;
            /** @type {number} */
            counter[counter.length - 1] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._findStart = function() {
      var leadingWhitespaceStart;
      var startInfo;
      var self = this;
      var offset = self._nextSet(self._row);
      /** @type {number} */
      var rtlCorrection = 1;
      for (; !startInfo;) {
        if (!(startInfo = self._findPattern(self.START_PATTERN, offset, false, true))) {
          return null;
        }
        if (rtlCorrection = Math.floor((startInfo.end - startInfo.start) / DyMilli), (leadingWhitespaceStart = startInfo.start - 5 * rtlCorrection) >= 0 && self._matchRange(leadingWhitespaceStart, startInfo.start, 0)) {
          return startInfo;
        }
        offset = startInfo.end;
        /** @type {null} */
        startInfo = null;
      }
    };
    /**
     * @param {!Object} endInfo
     * @return {?}
     */
    I2of5Reader.prototype._verifyTrailingWhitespace = function(endInfo) {
      var trailingWhitespaceEnd;
      var self = this;
      return trailingWhitespaceEnd = endInfo.end + (endInfo.end - endInfo.start) / 2, trailingWhitespaceEnd < self._row.length && self._matchRange(endInfo.end, trailingWhitespaceEnd, 0) ? endInfo : null;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._findEnd = function() {
      var endInfo;
      var tmp;
      var offset;
      var self = this;
      return self._row.reverse(), offset = self._nextSet(self._row), endInfo = self._findPattern(self.STOP_PATTERN, offset, false, true), self._row.reverse(), null === endInfo ? null : (tmp = endInfo.start, endInfo.start = self._row.length - endInfo.end, endInfo.end = self._row.length - tmp, null !== endInfo ? self._verifyTrailingWhitespace(endInfo) : null);
    };
    /**
     * @param {!Array} counter
     * @return {?}
     */
    I2of5Reader.prototype._decodeCode = function(counter) {
      var i;
      var error;
      var code;
      var self = this;
      /** @type {number} */
      var sum = 0;
      var epsilon = self.AVG_CODE_ERROR;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0
      };
      /** @type {number} */
      i = 0;
      for (; i < counter.length; i++) {
        sum = sum + counter[i];
      }
      /** @type {number} */
      code = 0;
      for (; code < self.CODE_PATTERN.length; code++) {
        if ((error = self._matchPattern(counter, self.CODE_PATTERN[code])) < bestMatch.error) {
          /** @type {number} */
          bestMatch.code = code;
          bestMatch.error = error;
        }
      }
      if (bestMatch.error < epsilon) {
        return bestMatch;
      }
    };
    /**
     * @param {number} counters
     * @param {!Array} result
     * @param {!Array} decodedCodes
     * @return {?}
     */
    I2of5Reader.prototype._decodePayload = function(counters, result, decodedCodes) {
      var next;
      var code;
      var self = this;
      /** @type {number} */
      var pos = 0;
      var counterLength = counters.length;
      /** @type {!Array} */
      var i = [0, 0, 0, 0, 0];
      for (; pos < counterLength;) {
        /** @type {number} */
        next = 0;
        for (; next < 5; next++) {
          /** @type {number} */
          i[next] = counters[pos] * this.barSpaceRatio[0];
          /** @type {number} */
          pos = pos + 2;
        }
        if (!(code = self._decodeCode(i))) {
          return null;
        }
        result.push(code.code + "");
        decodedCodes.push(code);
      }
      return code;
    };
    /**
     * @param {number} counters
     * @return {?}
     */
    I2of5Reader.prototype._verifyCounterLength = function(counters) {
      return counters.length % 10 == 0;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._decode = function() {
      var startInfo;
      var endInfo;
      var counters;
      var self = this;
      /** @type {!Array} */
      var result = [];
      /** @type {!Array} */
      var decodedCodes = [];
      return (startInfo = self._findStart()) ? (decodedCodes.push(startInfo), (endInfo = self._findEnd()) ? (counters = self._fillCounters(startInfo.end, endInfo.start, false), self._verifyCounterLength(counters) && self._decodePayload(counters, result, decodedCodes) ? result.length < 5 ? null : (decodedCodes.push(endInfo), {
        code : result.join(""),
        start : startInfo.start,
        end : endInfo.end,
        startInfo : startInfo,
        decodedCodes : decodedCodes
      }) : null) : null) : null;
    };
    /** @type {function(?): undefined} */
    defaultTagAttributes.a = I2of5Reader;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @return {undefined}
     */
    function CodabarReader() {
      o.a.call(this);
      /** @type {!Array} */
      this._counters = [];
    }
    var o = keyGen(1);
    var properties = {
      ALPHABETH_STRING : {
        value : "0123456789-$:/.+ABCD"
      },
      ALPHABET : {
        value : [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 45, 36, 58, 47, 46, 43, 65, 66, 67, 68]
      },
      CHARACTER_ENCODINGS : {
        value : [3, 6, 9, 96, 18, 66, 33, 36, 48, 72, 12, 24, 69, 81, 84, 21, 26, 41, 11, 14]
      },
      START_END : {
        value : [26, 41, 11, 14]
      },
      MIN_ENCODED_CHARS : {
        value : 4
      },
      MAX_ACCEPTABLE : {
        value : 2
      },
      PADDING : {
        value : 1.5
      },
      FORMAT : {
        value : "codabar",
        writeable : false
      }
    };
    /** @type {!Object} */
    CodabarReader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    CodabarReader.prototype.constructor = CodabarReader;
    /**
     * @return {?}
     */
    CodabarReader.prototype._decode = function() {
      var start;
      var pt;
      var pattern;
      var nextStart;
      var viz_run;
      var self = this;
      /** @type {!Array} */
      var result = [];
      if (this._counters = self._fillCounters(), !(start = self._findStart())) {
        return null;
      }
      nextStart = start.startCounter;
      do {
        if ((pattern = self._toPattern(nextStart)) < 0) {
          return null;
        }
        if ((pt = self._patternToChar(pattern)) < 0) {
          return null;
        }
        if (result.push(pt), nextStart = nextStart + 8, result.length > 1 && self._isStartEnd(pattern)) {
          break;
        }
      } while (nextStart < self._counters.length);
      return result.length - 2 < self.MIN_ENCODED_CHARS || !self._isStartEnd(pattern) ? null : self._verifyWhitespace(start.startCounter, nextStart - 8) && self._validateResult(result, start.startCounter) ? (nextStart = nextStart > self._counters.length ? self._counters.length : nextStart, viz_run = start.start + self._sumCounters(start.startCounter, nextStart - 8), {
        code : result.join(""),
        start : start.start,
        end : viz_run,
        startInfo : start,
        decodedCodes : result
      }) : null;
    };
    /**
     * @param {number} startCounter
     * @param {number} endCounter
     * @return {?}
     */
    CodabarReader.prototype._verifyWhitespace = function(startCounter, endCounter) {
      return (startCounter - 1 <= 0 || this._counters[startCounter - 1] >= this._calculatePatternLength(startCounter) / 2) && (endCounter + 8 >= this._counters.length || this._counters[endCounter + 7] >= this._calculatePatternLength(endCounter) / 2);
    };
    /**
     * @param {number} offset
     * @return {?}
     */
    CodabarReader.prototype._calculatePatternLength = function(offset) {
      var i;
      /** @type {number} */
      var sum = 0;
      /** @type {number} */
      i = offset;
      for (; i < offset + 7; i++) {
        sum = sum + this._counters[i];
      }
      return sum;
    };
    /**
     * @param {!Array} result
     * @param {?} startCounter
     * @return {?}
     */
    CodabarReader.prototype._thresholdResultPattern = function(result, startCounter) {
      var is;
      var cam;
      var i;
      var j;
      var carry;
      var self = this;
      var categorization = {
        space : {
          narrow : {
            size : 0,
            counts : 0,
            min : 0,
            max : Number.MAX_VALUE
          },
          wide : {
            size : 0,
            counts : 0,
            min : 0,
            max : Number.MAX_VALUE
          }
        },
        bar : {
          narrow : {
            size : 0,
            counts : 0,
            min : 0,
            max : Number.MAX_VALUE
          },
          wide : {
            size : 0,
            counts : 0,
            min : 0,
            max : Number.MAX_VALUE
          }
        }
      };
      var pos = startCounter;
      /** @type {number} */
      i = 0;
      for (; i < result.length; i++) {
        carry = self._charToPattern(result[i]);
        /** @type {number} */
        j = 6;
        for (; j >= 0; j--) {
          /** @type {({narrow: {counts: number, max: number, min: number, size: number}, wide: {counts: number, max: number, min: number, size: number}})} */
          is = 2 == (1 & j) ? categorization.bar : categorization.space;
          /** @type {({counts: number, max: number, min: number, size: number})} */
          cam = 1 == (1 & carry) ? is.wide : is.narrow;
          cam.size += self._counters[pos + j];
          cam.counts++;
          /** @type {number} */
          carry = carry >> 1;
        }
        pos = pos + 8;
      }
      return ["space", "bar"].forEach(function(key) {
        var newkind = categorization[key];
        /** @type {number} */
        newkind.wide.min = Math.floor((newkind.narrow.size / newkind.narrow.counts + newkind.wide.size / newkind.wide.counts) / 2);
        /** @type {number} */
        newkind.narrow.max = Math.ceil(newkind.wide.min);
        /** @type {number} */
        newkind.wide.max = Math.ceil((newkind.wide.size * self.MAX_ACCEPTABLE + self.PADDING) / newkind.wide.counts);
      }), categorization;
    };
    /**
     * @param {string} char
     * @return {?}
     */
    CodabarReader.prototype._charToPattern = function(char) {
      var i;
      var self = this;
      var tmpDateStr = char.charCodeAt(0);
      /** @type {number} */
      i = 0;
      for (; i < self.ALPHABET.length; i++) {
        if (self.ALPHABET[i] === tmpDateStr) {
          return self.CHARACTER_ENCODINGS[i];
        }
      }
      return 0;
    };
    /**
     * @param {!Array} result
     * @param {?} startCounter
     * @return {?}
     */
    CodabarReader.prototype._validateResult = function(result, startCounter) {
      var i;
      var j;
      var is;
      var coll;
      var k;
      var t;
      var self = this;
      var thresholds = self._thresholdResultPattern(result, startCounter);
      var pos = startCounter;
      /** @type {number} */
      i = 0;
      for (; i < result.length; i++) {
        t = self._charToPattern(result[i]);
        /** @type {number} */
        j = 6;
        for (; j >= 0; j--) {
          if (is = 0 == (1 & j) ? thresholds.bar : thresholds.space, coll = 1 == (1 & t) ? is.wide : is.narrow, (k = self._counters[pos + j]) < coll.min || k > coll.max) {
            return false;
          }
          /** @type {number} */
          t = t >> 1;
        }
        pos = pos + 8;
      }
      return true;
    };
    /**
     * @param {!Object} pattern
     * @return {?}
     */
    CodabarReader.prototype._patternToChar = function(pattern) {
      var i;
      var self = this;
      /** @type {number} */
      i = 0;
      for (; i < self.CHARACTER_ENCODINGS.length; i++) {
        if (self.CHARACTER_ENCODINGS[i] === pattern) {
          return String.fromCharCode(self.ALPHABET[i]);
        }
      }
      return -1;
    };
    /**
     * @param {number} offset
     * @param {number} end
     * @return {?}
     */
    CodabarReader.prototype._computeAlternatingThreshold = function(offset, end) {
      var i;
      var counter;
      /** @type {number} */
      var min = Number.MAX_VALUE;
      /** @type {number} */
      var max = 0;
      /** @type {number} */
      i = offset;
      for (; i < end; i = i + 2) {
        counter = this._counters[i];
        if (counter > max) {
          max = counter;
        }
        if (counter < min) {
          min = counter;
        }
      }
      return (min + max) / 2 | 0;
    };
    /**
     * @param {number} offset
     * @return {?}
     */
    CodabarReader.prototype._toPattern = function(offset) {
      var barThreshold;
      var spaceThreshold;
      var i;
      var threshold;
      /** @type {number} */
      var length = 7;
      var end = offset + length;
      /** @type {number} */
      var bitmask = 1 << length - 1;
      /** @type {number} */
      var pattern = 0;
      if (end > this._counters.length) {
        return -1;
      }
      barThreshold = this._computeAlternatingThreshold(offset, end);
      spaceThreshold = this._computeAlternatingThreshold(offset + 1, end);
      /** @type {number} */
      i = 0;
      for (; i < length; i++) {
        threshold = 0 == (1 & i) ? barThreshold : spaceThreshold;
        if (this._counters[offset + i] > threshold) {
          /** @type {number} */
          pattern = pattern | bitmask;
        }
        /** @type {number} */
        bitmask = bitmask >> 1;
      }
      return pattern;
    };
    /**
     * @param {!Object} pattern
     * @return {?}
     */
    CodabarReader.prototype._isStartEnd = function(pattern) {
      var i;
      /** @type {number} */
      i = 0;
      for (; i < this.START_END.length; i++) {
        if (this.START_END[i] === pattern) {
          return true;
        }
      }
      return false;
    };
    /**
     * @param {number} start
     * @param {number} end
     * @return {?}
     */
    CodabarReader.prototype._sumCounters = function(start, end) {
      var i;
      /** @type {number} */
      var sum = 0;
      /** @type {number} */
      i = start;
      for (; i < end; i++) {
        sum = sum + this._counters[i];
      }
      return sum;
    };
    /**
     * @return {?}
     */
    CodabarReader.prototype._findStart = function() {
      var i;
      var pattern;
      var newTop;
      var self = this;
      var t = self._nextUnset(self._row);
      /** @type {number} */
      i = 1;
      for (; i < this._counters.length; i++) {
        if ((pattern = self._toPattern(i)) !== -1 && self._isStartEnd(pattern)) {
          return t = t + self._sumCounters(0, i), newTop = t + self._sumCounters(i, i + 8), {
            start : t,
            end : newTop,
            startCounter : i,
            endCounter : i + 8
          };
        }
      }
    };
    /** @type {function(): undefined} */
    defaultTagAttributes.a = CodabarReader;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @return {undefined}
     */
    function Code128Reader() {
      o.a.call(this);
    }
    /**
     * @param {?} expected
     * @param {!Array} normalized
     * @param {!Array} indices
     * @return {?}
     */
    function calculateCorrection(expected, normalized, indices) {
      var length = indices.length;
      /** @type {number} */
      var sumNormalized = 0;
      /** @type {number} */
      var sumExpected = 0;
      for (; length--;) {
        sumExpected = sumExpected + expected[indices[length]];
        sumNormalized = sumNormalized + normalized[indices[length]];
      }
      return sumExpected / sumNormalized;
    }
    var o = keyGen(1);
    var properties = {
      CODE_SHIFT : {
        value : 98
      },
      CODE_C : {
        value : 99
      },
      CODE_B : {
        value : 100
      },
      CODE_A : {
        value : 101
      },
      START_CODE_A : {
        value : 103
      },
      START_CODE_B : {
        value : 104
      },
      START_CODE_C : {
        value : 105
      },
      STOP_CODE : {
        value : 106
      },
      CODE_PATTERN : {
        value : [[2, 1, 2, 2, 2, 2], [2, 2, 2, 1, 2, 2], [2, 2, 2, 2, 2, 1], [1, 2, 1, 2, 2, 3], [1, 2, 1, 3, 2, 2], [1, 3, 1, 2, 2, 2], [1, 2, 2, 2, 1, 3], [1, 2, 2, 3, 1, 2], [1, 3, 2, 2, 1, 2], [2, 2, 1, 2, 1, 3], [2, 2, 1, 3, 1, 2], [2, 3, 1, 2, 1, 2], [1, 1, 2, 2, 3, 2], [1, 2, 2, 1, 3, 2], [1, 2, 2, 2, 3, 1], [1, 1, 3, 2, 2, 2], [1, 2, 3, 1, 2, 2], [1, 2, 3, 2, 2, 1], [2, 2, 3, 2, 1, 1], [2, 2, 1, 1, 3, 2], [2, 2, 1, 2, 3, 1], [2, 1, 3, 2, 1, 2], [2, 2, 3, 1, 1, 2], [3, 1, 2, 1, 3, 1], [3, 
        1, 1, 2, 2, 2], [3, 2, 1, 1, 2, 2], [3, 2, 1, 2, 2, 1], [3, 1, 2, 2, 1, 2], [3, 2, 2, 1, 1, 2], [3, 2, 2, 2, 1, 1], [2, 1, 2, 1, 2, 3], [2, 1, 2, 3, 2, 1], [2, 3, 2, 1, 2, 1], [1, 1, 1, 3, 2, 3], [1, 3, 1, 1, 2, 3], [1, 3, 1, 3, 2, 1], [1, 1, 2, 3, 1, 3], [1, 3, 2, 1, 1, 3], [1, 3, 2, 3, 1, 1], [2, 1, 1, 3, 1, 3], [2, 3, 1, 1, 1, 3], [2, 3, 1, 3, 1, 1], [1, 1, 2, 1, 3, 3], [1, 1, 2, 3, 3, 1], [1, 3, 2, 1, 3, 1], [1, 1, 3, 1, 2, 3], [1, 1, 3, 3, 2, 1], [1, 3, 3, 1, 2, 1], [3, 1, 3, 1, 2, 1], 
        [2, 1, 1, 3, 3, 1], [2, 3, 1, 1, 3, 1], [2, 1, 3, 1, 1, 3], [2, 1, 3, 3, 1, 1], [2, 1, 3, 1, 3, 1], [3, 1, 1, 1, 2, 3], [3, 1, 1, 3, 2, 1], [3, 3, 1, 1, 2, 1], [3, 1, 2, 1, 1, 3], [3, 1, 2, 3, 1, 1], [3, 3, 2, 1, 1, 1], [3, 1, 4, 1, 1, 1], [2, 2, 1, 4, 1, 1], [4, 3, 1, 1, 1, 1], [1, 1, 1, 2, 2, 4], [1, 1, 1, 4, 2, 2], [1, 2, 1, 1, 2, 4], [1, 2, 1, 4, 2, 1], [1, 4, 1, 1, 2, 2], [1, 4, 1, 2, 2, 1], [1, 1, 2, 2, 1, 4], [1, 1, 2, 4, 1, 2], [1, 2, 2, 1, 1, 4], [1, 2, 2, 4, 1, 1], [1, 4, 2, 1, 
        1, 2], [1, 4, 2, 2, 1, 1], [2, 4, 1, 2, 1, 1], [2, 2, 1, 1, 1, 4], [4, 1, 3, 1, 1, 1], [2, 4, 1, 1, 1, 2], [1, 3, 4, 1, 1, 1], [1, 1, 1, 2, 4, 2], [1, 2, 1, 1, 4, 2], [1, 2, 1, 2, 4, 1], [1, 1, 4, 2, 1, 2], [1, 2, 4, 1, 1, 2], [1, 2, 4, 2, 1, 1], [4, 1, 1, 2, 1, 2], [4, 2, 1, 1, 1, 2], [4, 2, 1, 2, 1, 1], [2, 1, 2, 1, 4, 1], [2, 1, 4, 1, 2, 1], [4, 1, 2, 1, 2, 1], [1, 1, 1, 1, 4, 3], [1, 1, 1, 3, 4, 1], [1, 3, 1, 1, 4, 1], [1, 1, 4, 1, 1, 3], [1, 1, 4, 3, 1, 1], [4, 1, 1, 1, 1, 3], [4, 1, 
        1, 3, 1, 1], [1, 1, 3, 1, 4, 1], [1, 1, 4, 1, 3, 1], [3, 1, 1, 1, 4, 1], [4, 1, 1, 1, 3, 1], [2, 1, 1, 4, 1, 2], [2, 1, 1, 2, 1, 4], [2, 1, 1, 2, 3, 2], [2, 3, 3, 1, 1, 1, 2]]
      },
      SINGLE_CODE_ERROR : {
        value : .64
      },
      AVG_CODE_ERROR : {
        value : .3
      },
      FORMAT : {
        value : "code_128",
        writeable : false
      },
      MODULE_INDICES : {
        value : {
          bar : [0, 2, 4],
          space : [1, 3, 5]
        }
      }
    };
    /** @type {!Object} */
    Code128Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    Code128Reader.prototype.constructor = Code128Reader;
    /**
     * @param {number} start
     * @param {!Object} correction
     * @return {?}
     */
    Code128Reader.prototype._decodeCode = function(start, correction) {
      var i;
      var code;
      var error;
      /** @type {!Array} */
      var counter = [0, 0, 0, 0, 0, 0];
      var self = this;
      /** @type {number} */
      var offset = start;
      /** @type {boolean} */
      var isWhite = !self._row[offset];
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : start,
        end : start,
        correction : {
          bar : 1,
          space : 1
        }
      };
      i = offset;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            if (correction) {
              self._correct(counter, correction);
            }
            /** @type {number} */
            code = 0;
            for (; code < self.CODE_PATTERN.length; code++) {
              if ((error = self._matchPattern(counter, self.CODE_PATTERN[code])) < bestMatch.error) {
                /** @type {number} */
                bestMatch.code = code;
                bestMatch.error = error;
              }
            }
            return bestMatch.end = i, bestMatch.code === -1 || bestMatch.error > self.AVG_CODE_ERROR ? null : (self.CODE_PATTERN[bestMatch.code] && (bestMatch.correction.bar = calculateCorrection(self.CODE_PATTERN[bestMatch.code], counter, this.MODULE_INDICES.bar), bestMatch.correction.space = calculateCorrection(self.CODE_PATTERN[bestMatch.code], counter, this.MODULE_INDICES.space)), bestMatch);
          }
          name++;
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @param {!Array} counter
     * @param {!Object} correction
     * @return {undefined}
     */
    Code128Reader.prototype._correct = function(counter, correction) {
      this._correctBars(counter, correction.bar, this.MODULE_INDICES.bar);
      this._correctBars(counter, correction.space, this.MODULE_INDICES.space);
    };
    /**
     * @return {?}
     */
    Code128Reader.prototype._findStart = function() {
      var i;
      var code;
      var error;
      var j;
      var sum;
      /** @type {!Array} */
      var counter = [0, 0, 0, 0, 0, 0];
      var self = this;
      var contactCapacity = self._nextSet(self._row);
      /** @type {boolean} */
      var isWhite = false;
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0,
        correction : {
          bar : 1,
          space : 1
        }
      };
      i = contactCapacity;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            /** @type {number} */
            sum = 0;
            /** @type {number} */
            j = 0;
            for (; j < counter.length; j++) {
              sum = sum + counter[j];
            }
            code = self.START_CODE_A;
            for (; code <= self.START_CODE_C; code++) {
              if ((error = self._matchPattern(counter, self.CODE_PATTERN[code])) < bestMatch.error) {
                bestMatch.code = code;
                bestMatch.error = error;
              }
            }
            if (bestMatch.error < self.AVG_CODE_ERROR) {
              return bestMatch.start = i - sum, bestMatch.end = i, bestMatch.correction.bar = calculateCorrection(self.CODE_PATTERN[bestMatch.code], counter, this.MODULE_INDICES.bar), bestMatch.correction.space = calculateCorrection(self.CODE_PATTERN[bestMatch.code], counter, this.MODULE_INDICES.space), bestMatch;
            }
            /** @type {number} */
            j = 0;
            for (; j < 4; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[4] = 0;
            /** @type {number} */
            counter[5] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @return {?}
     */
    Code128Reader.prototype._decode = function() {
      var codeset;
      var hotCurrentParentsTemp;
      var self = this;
      var startInfo = self._findStart();
      /** @type {null} */
      var code = null;
      /** @type {boolean} */
      var i = false;
      /** @type {!Array} */
      var a = [];
      /** @type {number} */
      var multiplier = 0;
      /** @type {number} */
      var checksum = 0;
      /** @type {!Array} */
      var rawResult = [];
      /** @type {!Array} */
      var decodedCodes = [];
      /** @type {boolean} */
      var hotCurrentParents = false;
      /** @type {boolean} */
      var previous = true;
      if (null === startInfo) {
        return null;
      }
      switch(code = {
        code : startInfo.code,
        start : startInfo.start,
        end : startInfo.end,
        correction : {
          bar : startInfo.correction.bar,
          space : startInfo.correction.space
        }
      }, decodedCodes.push(code), checksum = code.code, code.code) {
        case self.START_CODE_A:
          codeset = self.CODE_A;
          break;
        case self.START_CODE_B:
          codeset = self.CODE_B;
          break;
        case self.START_CODE_C:
          codeset = self.CODE_C;
          break;
        default:
          return null;
      }
      for (; !i;) {
        if (hotCurrentParentsTemp = hotCurrentParents, hotCurrentParents = false, null !== (code = self._decodeCode(code.end, code.correction))) {
          switch(code.code !== self.STOP_CODE && (previous = true), code.code !== self.STOP_CODE && (rawResult.push(code.code), multiplier++, checksum = checksum + multiplier * code.code), decodedCodes.push(code), codeset) {
            case self.CODE_A:
              if (code.code < 64) {
                a.push(String.fromCharCode(32 + code.code));
              } else {
                if (code.code < 96) {
                  a.push(String.fromCharCode(code.code - 64));
                } else {
                  switch(code.code !== self.STOP_CODE && (previous = false), code.code) {
                    case self.CODE_SHIFT:
                      /** @type {boolean} */
                      hotCurrentParents = true;
                      codeset = self.CODE_B;
                      break;
                    case self.CODE_B:
                      codeset = self.CODE_B;
                      break;
                    case self.CODE_C:
                      codeset = self.CODE_C;
                      break;
                    case self.STOP_CODE:
                      /** @type {boolean} */
                      i = true;
                  }
                }
              }
              break;
            case self.CODE_B:
              if (code.code < 96) {
                a.push(String.fromCharCode(32 + code.code));
              } else {
                switch(code.code !== self.STOP_CODE && (previous = false), code.code) {
                  case self.CODE_SHIFT:
                    /** @type {boolean} */
                    hotCurrentParents = true;
                    codeset = self.CODE_A;
                    break;
                  case self.CODE_A:
                    codeset = self.CODE_A;
                    break;
                  case self.CODE_C:
                    codeset = self.CODE_C;
                    break;
                  case self.STOP_CODE:
                    /** @type {boolean} */
                    i = true;
                }
              }
              break;
            case self.CODE_C:
              if (code.code < 100) {
                a.push(code.code < 10 ? "0" + code.code : code.code);
              } else {
                switch(code.code !== self.STOP_CODE && (previous = false), code.code) {
                  case self.CODE_A:
                    codeset = self.CODE_A;
                    break;
                  case self.CODE_B:
                    codeset = self.CODE_B;
                    break;
                  case self.STOP_CODE:
                    /** @type {boolean} */
                    i = true;
                }
              }
          }
        } else {
          /** @type {boolean} */
          i = true;
        }
        if (hotCurrentParentsTemp) {
          codeset = codeset === self.CODE_A ? self.CODE_B : self.CODE_A;
        }
      }
      return null === code ? null : (code.end = self._nextUnset(self._row, code.end), self._verifyTrailingWhitespace(code) ? (checksum = checksum - multiplier * rawResult[rawResult.length - 1]) % 103 !== rawResult[rawResult.length - 1] ? null : a.length ? (previous && a.splice(a.length - 1, 1), {
        code : a.join(""),
        start : startInfo.start,
        end : code.end,
        codeset : codeset,
        startInfo : startInfo,
        decodedCodes : decodedCodes,
        endInfo : code
      }) : null : null);
    };
    /**
     * @param {!Object} endInfo
     * @return {?}
     */
    o.a.prototype._verifyTrailingWhitespace = function(endInfo) {
      var trailingWhitespaceEnd;
      var self = this;
      return trailingWhitespaceEnd = endInfo.end + (endInfo.end - endInfo.start) / 2, trailingWhitespaceEnd < self._row.length && self._matchRange(endInfo.end, trailingWhitespaceEnd, 0) ? endInfo : null;
    };
    /** @type {function(): undefined} */
    defaultTagAttributes.a = Code128Reader;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @return {undefined}
     */
    function Code39VINReader() {
      o.a.call(this);
    }
    var o = keyGen(31);
    var patterns = {
      IOQ : /[IOQ]/g,
      AZ09 : /[A-Z0-9]{17}/
    };
    /** @type {!Object} */
    Code39VINReader.prototype = Object.create(o.a.prototype);
    /** @type {function(): undefined} */
    Code39VINReader.prototype.constructor = Code39VINReader;
    /**
     * @return {?}
     */
    Code39VINReader.prototype._decode = function() {
      var event = o.a.prototype._decode.apply(this);
      if (!event) {
        return null;
      }
      var code = event.code;
      return code ? (code = code.replace(patterns.IOQ, ""), code.match(patterns.AZ09) && this._checkChecksum(code) ? (event.code = code, event) : null) : null;
    };
    /**
     * @param {?} code
     * @return {?}
     */
    Code39VINReader.prototype._checkChecksum = function(code) {
      return !!code;
    };
    /** @type {function(): undefined} */
    defaultTagAttributes.a = Code39VINReader;
  }, function(canCreateDiscussions, defaultTagAttributes, require) {
    /**
     * @return {undefined}
     */
    function Code93Reader() {
      o.a.call(this);
    }
    var o = require(1);
    var store = require(3);
    /** @type {string} */
    var p = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%abcd*";
    var properties = {
      ALPHABETH_STRING : {
        value : p
      },
      ALPHABET : {
        value : p.split("").map(function(strUtf8) {
          return strUtf8.charCodeAt(0);
        })
      },
      CHARACTER_ENCODINGS : {
        value : [276, 328, 324, 322, 296, 292, 290, 336, 274, 266, 424, 420, 418, 404, 402, 394, 360, 356, 354, 308, 282, 344, 332, 326, 300, 278, 436, 434, 428, 422, 406, 410, 364, 358, 310, 314, 302, 468, 466, 458, 366, 374, 430, 294, 474, 470, 306, 350]
      },
      ASTERISK : {
        value : 350
      },
      FORMAT : {
        value : "code_93",
        writeable : false
      }
    };
    /** @type {!Object} */
    Code93Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    Code93Reader.prototype.constructor = Code93Reader;
    /**
     * @return {?}
     */
    Code93Reader.prototype._decode = function() {
      var pt;
      var lastStart;
      var pattern;
      var nextStart;
      var self = this;
      /** @type {!Array} */
      var counters = [0, 0, 0, 0, 0, 0];
      /** @type {!Array} */
      var result = [];
      var start = self._findStart();
      if (!start) {
        return null;
      }
      nextStart = self._nextSet(self._row, start.end);
      do {
        if (counters = self._toCounters(nextStart, counters), (pattern = self._toPattern(counters)) < 0) {
          return null;
        }
        if ((pt = self._patternToChar(pattern)) < 0) {
          return null;
        }
        result.push(pt);
        lastStart = nextStart;
        nextStart = nextStart + store.a.sum(counters);
        nextStart = self._nextSet(self._row, nextStart);
      } while ("*" !== pt);
      return result.pop(), result.length && self._verifyEnd(lastStart, nextStart, counters) && self._verifyChecksums(result) ? (result = result.slice(0, result.length - 2), null === (result = self._decodeExtended(result)) ? null : {
        code : result.join(""),
        start : start.start,
        end : nextStart,
        startInfo : start,
        decodedCodes : result
      }) : null;
    };
    /**
     * @param {!Object} lastStart
     * @param {!Object} nextStart
     * @return {?}
     */
    Code93Reader.prototype._verifyEnd = function(lastStart, nextStart) {
      return !(lastStart === nextStart || !this._row[nextStart]);
    };
    /**
     * @param {!Object} pattern
     * @return {?}
     */
    Code93Reader.prototype._patternToChar = function(pattern) {
      var i;
      var self = this;
      /** @type {number} */
      i = 0;
      for (; i < self.CHARACTER_ENCODINGS.length; i++) {
        if (self.CHARACTER_ENCODINGS[i] === pattern) {
          return String.fromCharCode(self.ALPHABET[i]);
        }
      }
      return -1;
    };
    /**
     * @param {!Array} counters
     * @return {?}
     */
    Code93Reader.prototype._toPattern = function(counters) {
      var counterLength = counters.length;
      /** @type {number} */
      var pattern = 0;
      /** @type {number} */
      var total = 0;
      /** @type {number} */
      var i = 0;
      for (; i < counterLength; i++) {
        total = total + counters[i];
      }
      /** @type {number} */
      var pos = 0;
      for (; pos < counterLength; pos++) {
        /** @type {number} */
        var normalized = Math.round(9 * counters[pos] / total);
        if (normalized < 1 || normalized > 4) {
          return -1;
        }
        if (0 == (1 & pos)) {
          /** @type {number} */
          var i = 0;
          for (; i < normalized; i++) {
            /** @type {number} */
            pattern = pattern << 1 | 1;
          }
        } else {
          /** @type {number} */
          pattern = pattern << normalized;
        }
      }
      return pattern;
    };
    /**
     * @return {?}
     */
    Code93Reader.prototype._findStart = function() {
      var i;
      var j;
      var e;
      var self = this;
      var point = self._nextSet(self._row);
      var b = point;
      /** @type {!Array} */
      var counter = [0, 0, 0, 0, 0, 0];
      /** @type {number} */
      var name = 0;
      /** @type {boolean} */
      var isWhite = false;
      i = point;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            if (self._toPattern(counter) === self.ASTERISK && (e = Math.floor(Math.max(0, b - (i - b) / 4)), self._matchRange(e, b, 0))) {
              return {
                start : b,
                end : i
              };
            }
            b = b + (counter[0] + counter[1]);
            /** @type {number} */
            j = 0;
            for (; j < 4; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[4] = 0;
            /** @type {number} */
            counter[5] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @param {!Array} str
     * @return {?}
     */
    Code93Reader.prototype._decodeExtended = function(str) {
      var l = str.length;
      /** @type {!Array} */
      var result = [];
      /** @type {number} */
      var i = 0;
      for (; i < l; i++) {
        var char = str[i];
        if (char >= "a" && char <= "d") {
          if (i > l - 2) {
            return null;
          }
          var nextChar = str[++i];
          var code = nextChar.charCodeAt(0);
          var embedResult = void 0;
          switch(char) {
            case "a":
              if (!(nextChar >= "A" && nextChar <= "Z")) {
                return null;
              }
              /** @type {string} */
              embedResult = String.fromCharCode(code - 64);
              break;
            case "b":
              if (nextChar >= "A" && nextChar <= "E") {
                /** @type {string} */
                embedResult = String.fromCharCode(code - 38);
              } else {
                if (nextChar >= "F" && nextChar <= "J") {
                  /** @type {string} */
                  embedResult = String.fromCharCode(code - 11);
                } else {
                  if (nextChar >= "K" && nextChar <= "O") {
                    /** @type {string} */
                    embedResult = String.fromCharCode(code + 16);
                  } else {
                    if (nextChar >= "P" && nextChar <= "S") {
                      /** @type {string} */
                      embedResult = String.fromCharCode(code + 43);
                    } else {
                      if (!(nextChar >= "T" && nextChar <= "Z")) {
                        return null;
                      }
                      /** @type {string} */
                      embedResult = String.fromCharCode(127);
                    }
                  }
                }
              }
              break;
            case "c":
              if (nextChar >= "A" && nextChar <= "O") {
                /** @type {string} */
                embedResult = String.fromCharCode(code - 32);
              } else {
                if ("Z" !== nextChar) {
                  return null;
                }
                /** @type {string} */
                embedResult = ":";
              }
              break;
            case "d":
              if (!(nextChar >= "A" && nextChar <= "Z")) {
                return null;
              }
              /** @type {string} */
              embedResult = String.fromCharCode(code + 32);
          }
          result.push(embedResult);
        } else {
          result.push(char);
        }
      }
      return result;
    };
    /**
     * @param {!Array} charArray
     * @return {?}
     */
    Code93Reader.prototype._verifyChecksums = function(charArray) {
      return this._matchCheckChar(charArray, charArray.length - 2, 20) && this._matchCheckChar(charArray, charArray.length - 1, 15);
    };
    /**
     * @param {!Array} charArray
     * @param {number} index
     * @param {number} maxWeight
     * @return {?}
     */
    Code93Reader.prototype._matchCheckChar = function(charArray, index, maxWeight) {
      var self = this;
      var o = charArray.slice(0, index);
      var c = o.length;
      var weightedSums = o.reduce(function(canCreateDiscussions, strUtf8, result) {
        return canCreateDiscussions + ((result * -1 + (c - 1)) % maxWeight + 1) * self.ALPHABET.indexOf(strUtf8.charCodeAt(0));
      }, 0);
      return this.ALPHABET[weightedSums % 47] === charArray[index].charCodeAt(0);
    };
    /** @type {function(): undefined} */
    defaultTagAttributes.a = Code93Reader;
  }, function(canCreateDiscussions, counters, keyGen) {
    /**
     * @return {undefined}
     */
    function c() {
      o.a.call(this);
    }
    var o = keyGen(4);
    var properties = {
      FORMAT : {
        value : "ean_2",
        writeable : false
      }
    };
    /** @type {!Object} */
    c.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    c.prototype.constructor = c;
    /**
     * @param {!Object} val
     * @param {?} start
     * @return {?}
     */
    c.prototype.decode = function(val, start) {
      /** @type {!Object} */
      this._row = val;
      var code;
      /** @type {number} */
      var r = 0;
      /** @type {number} */
      var i = 0;
      var offset = start;
      var end = this._row.length;
      /** @type {!Array} */
      var strBufferAry = [];
      /** @type {!Array} */
      var decodedCodes = [];
      /** @type {number} */
      i = 0;
      for (; i < 2 && offset < end; i++) {
        if (!(code = this._decodeCode(offset))) {
          return null;
        }
        decodedCodes.push(code);
        strBufferAry.push(code.code % 10);
        if (code.code >= this.CODE_G_START) {
          /** @type {number} */
          r = r | 1 << 1 - i;
        }
        if (1 != i) {
          offset = this._nextSet(this._row, code.end);
          offset = this._nextUnset(this._row, offset);
        }
      }
      return 2 != strBufferAry.length || parseInt(strBufferAry.join("")) % 4 !== r ? null : {
        code : strBufferAry.join(""),
        decodedCodes : decodedCodes,
        end : code.end
      };
    };
    /** @type {function(): undefined} */
    counters.a = c;
  }, function(canCreateDiscussions, counters, keyGen) {
    /**
     * @return {undefined}
     */
    function c() {
      o.a.call(this);
    }
    /**
     * @param {number} val
     * @return {?}
     */
    function evaluate(val) {
      var e;
      /** @type {number} */
      e = 0;
      for (; e < 10; e++) {
        if (val === tags[e]) {
          return e;
        }
      }
      return null;
    }
    /**
     * @param {!Array} t
     * @return {?}
     */
    function stringify(t) {
      var i;
      var urlCount = t.length;
      /** @type {number} */
      var acc = 0;
      /** @type {number} */
      i = urlCount - 2;
      for (; i >= 0; i = i - 2) {
        acc = acc + t[i];
      }
      /** @type {number} */
      acc = acc * 3;
      /** @type {number} */
      i = urlCount - 1;
      for (; i >= 0; i = i - 2) {
        acc = acc + t[i];
      }
      return (acc = acc * 3) % 10;
    }
    var o = keyGen(4);
    var properties = {
      FORMAT : {
        value : "ean_5",
        writeable : false
      }
    };
    /** @type {!Array} */
    var tags = [24, 20, 18, 17, 12, 6, 3, 10, 9, 5];
    /** @type {!Object} */
    c.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(): undefined} */
    c.prototype.constructor = c;
    /**
     * @param {!Object} val
     * @param {?} start
     * @return {?}
     */
    c.prototype.decode = function(val, start) {
      /** @type {!Object} */
      this._row = val;
      var code;
      /** @type {number} */
      var right = 0;
      /** @type {number} */
      var i = 0;
      var offset = start;
      var end = this._row.length;
      /** @type {!Array} */
      var left = [];
      /** @type {!Array} */
      var decodedCodes = [];
      /** @type {number} */
      i = 0;
      for (; i < 5 && offset < end; i++) {
        if (!(code = this._decodeCode(offset))) {
          return null;
        }
        decodedCodes.push(code);
        left.push(code.code % 10);
        if (code.code >= this.CODE_G_START) {
          /** @type {number} */
          right = right | 1 << 4 - i;
        }
        if (4 != i) {
          offset = this._nextSet(this._row, code.end);
          offset = this._nextUnset(this._row, offset);
        }
      }
      return 5 != left.length ? null : stringify(left) !== evaluate(right) ? null : {
        code : left.join(""),
        decodedCodes : decodedCodes,
        end : code.end
      };
    };
    /** @type {function(): undefined} */
    counters.a = c;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @param {?} opts
     * @param {?} supplements
     * @return {undefined}
     */
    function EAN8Reader(opts, supplements) {
      o.a.call(this, opts, supplements);
    }
    var o = keyGen(4);
    var properties = {
      FORMAT : {
        value : "ean_8",
        writeable : false
      }
    };
    /** @type {!Object} */
    EAN8Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?, ?): undefined} */
    EAN8Reader.prototype.constructor = EAN8Reader;
    /**
     * @param {!Object} code
     * @param {!Array} result
     * @param {!Array} decodedCodes
     * @return {?}
     */
    EAN8Reader.prototype._decodePayload = function(code, result, decodedCodes) {
      var r;
      var self = this;
      /** @type {number} */
      r = 0;
      for (; r < 4; r++) {
        if (!(code = self._decodeCode(code.end, self.CODE_G_START))) {
          return null;
        }
        result.push(code.code);
        decodedCodes.push(code);
      }
      if (null === (code = self._findPattern(self.MIDDLE_PATTERN, code.end, true, false))) {
        return null;
      }
      decodedCodes.push(code);
      /** @type {number} */
      r = 0;
      for (; r < 4; r++) {
        if (!(code = self._decodeCode(code.end, self.CODE_G_START))) {
          return null;
        }
        decodedCodes.push(code);
        result.push(code.code);
      }
      return code;
    };
    /** @type {function(?, ?): undefined} */
    defaultTagAttributes.a = EAN8Reader;
  }, function(canCreateDiscussions, defaultTagAttributes, fn) {
    /**
     * @param {?} opts
     * @return {undefined}
     */
    function I2of5Reader(opts) {
      opts = _req()(getDefaulConfig(), opts);
      o.a.call(this, opts);
      /** @type {!Array} */
      this.barSpaceRatio = [1, 1];
      if (opts.normalizeBarSpaceWidth) {
        /** @type {number} */
        this.SINGLE_CODE_ERROR = .38;
        /** @type {number} */
        this.AVG_CODE_ERROR = .09;
      }
    }
    /**
     * @return {?}
     */
    function getDefaulConfig() {
      var widgetData = {};
      return Object.keys(I2of5Reader.CONFIG_KEYS).forEach(function(key) {
        widgetData[key] = I2of5Reader.CONFIG_KEYS[key].default;
      }), widgetData;
    }
    var req = fn(28);
    var _req = fn.n(req);
    var o = fn(1);
    /** @type {number} */
    var N = 1;
    /** @type {number} */
    var emptySaveStrategy = 3;
    var properties = {
      START_PATTERN : {
        value : [N, N, N, N]
      },
      STOP_PATTERN : {
        value : [N, N, emptySaveStrategy]
      },
      CODE_PATTERN : {
        value : [[N, N, emptySaveStrategy, emptySaveStrategy, N], [emptySaveStrategy, N, N, N, emptySaveStrategy], [N, emptySaveStrategy, N, N, emptySaveStrategy], [emptySaveStrategy, emptySaveStrategy, N, N, N], [N, N, emptySaveStrategy, N, emptySaveStrategy], [emptySaveStrategy, N, emptySaveStrategy, N, N], [N, emptySaveStrategy, emptySaveStrategy, N, N], [N, N, N, emptySaveStrategy, emptySaveStrategy], [emptySaveStrategy, N, N, emptySaveStrategy, N], [N, emptySaveStrategy, N, emptySaveStrategy, 
        N]]
      },
      SINGLE_CODE_ERROR : {
        value : .78,
        writable : true
      },
      AVG_CODE_ERROR : {
        value : .38,
        writable : true
      },
      MAX_CORRECTION_FACTOR : {
        value : 5
      },
      FORMAT : {
        value : "i2of5"
      }
    };
    /** @type {!Object} */
    I2of5Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?): undefined} */
    I2of5Reader.prototype.constructor = I2of5Reader;
    /**
     * @param {!Array} counter
     * @param {!Object} code
     * @return {?}
     */
    I2of5Reader.prototype._matchPattern = function(counter, code) {
      if (this.config.normalizeBarSpaceWidth) {
        var i;
        /** @type {!Array} */
        var counterSum = [0, 0];
        /** @type {!Array} */
        var codeSum = [0, 0];
        /** @type {!Array} */
        var correction = [0, 0];
        var correctionRatio = this.MAX_CORRECTION_FACTOR;
        /** @type {number} */
        var correctionRatioInverse = 1 / correctionRatio;
        /** @type {number} */
        i = 0;
        for (; i < counter.length; i++) {
          counterSum[i % 2] += counter[i];
          codeSum[i % 2] += code[i];
        }
        /** @type {number} */
        correction[0] = codeSum[0] / counterSum[0];
        /** @type {number} */
        correction[1] = codeSum[1] / counterSum[1];
        /** @type {number} */
        correction[0] = Math.max(Math.min(correction[0], correctionRatio), correctionRatioInverse);
        /** @type {number} */
        correction[1] = Math.max(Math.min(correction[1], correctionRatio), correctionRatioInverse);
        /** @type {!Array} */
        this.barSpaceRatio = correction;
        /** @type {number} */
        i = 0;
        for (; i < counter.length; i++) {
          counter[i] *= this.barSpaceRatio[i % 2];
        }
      }
      return o.a.prototype._matchPattern.call(this, counter, code);
    };
    /**
     * @param {!Object} pattern
     * @param {number} offset
     * @param {boolean} isWhite
     * @param {string} tryHarder
     * @return {?}
     */
    I2of5Reader.prototype._findPattern = function(pattern, offset, isWhite, tryHarder) {
      var i;
      var error;
      var j;
      var sum;
      /** @type {!Array} */
      var counter = [];
      var self = this;
      /** @type {number} */
      var name = 0;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0
      };
      var epsilon = self.AVG_CODE_ERROR;
      isWhite = isWhite || false;
      tryHarder = tryHarder || false;
      if (!offset) {
        offset = self._nextSet(self._row);
      }
      /** @type {number} */
      i = 0;
      for (; i < pattern.length; i++) {
        /** @type {number} */
        counter[i] = 0;
      }
      /** @type {number} */
      i = offset;
      for (; i < self._row.length; i++) {
        if (self._row[i] ^ isWhite) {
          counter[name]++;
        } else {
          if (name === counter.length - 1) {
            /** @type {number} */
            sum = 0;
            /** @type {number} */
            j = 0;
            for (; j < counter.length; j++) {
              sum = sum + counter[j];
            }
            if ((error = self._matchPattern(counter, pattern)) < epsilon) {
              return bestMatch.error = error, bestMatch.start = i - sum, bestMatch.end = i, bestMatch;
            }
            if (!tryHarder) {
              return null;
            }
            /** @type {number} */
            j = 0;
            for (; j < counter.length - 2; j++) {
              counter[j] = counter[j + 2];
            }
            /** @type {number} */
            counter[counter.length - 2] = 0;
            /** @type {number} */
            counter[counter.length - 1] = 0;
            name--;
          } else {
            name++;
          }
          /** @type {number} */
          counter[name] = 1;
          /** @type {boolean} */
          isWhite = !isWhite;
        }
      }
      return null;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._findStart = function() {
      var leadingWhitespaceStart;
      var startInfo;
      var self = this;
      var offset = self._nextSet(self._row);
      /** @type {number} */
      var rtlCorrection = 1;
      for (; !startInfo;) {
        if (!(startInfo = self._findPattern(self.START_PATTERN, offset, false, true))) {
          return null;
        }
        if (rtlCorrection = Math.floor((startInfo.end - startInfo.start) / 4), (leadingWhitespaceStart = startInfo.start - 10 * rtlCorrection) >= 0 && self._matchRange(leadingWhitespaceStart, startInfo.start, 0)) {
          return startInfo;
        }
        offset = startInfo.end;
        /** @type {null} */
        startInfo = null;
      }
    };
    /**
     * @param {!Object} endInfo
     * @return {?}
     */
    I2of5Reader.prototype._verifyTrailingWhitespace = function(endInfo) {
      var trailingWhitespaceEnd;
      var self = this;
      return trailingWhitespaceEnd = endInfo.end + (endInfo.end - endInfo.start) / 2, trailingWhitespaceEnd < self._row.length && self._matchRange(endInfo.end, trailingWhitespaceEnd, 0) ? endInfo : null;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._findEnd = function() {
      var endInfo;
      var tmp;
      var self = this;
      return self._row.reverse(), endInfo = self._findPattern(self.STOP_PATTERN), self._row.reverse(), null === endInfo ? null : (tmp = endInfo.start, endInfo.start = self._row.length - endInfo.end, endInfo.end = self._row.length - tmp, null !== endInfo ? self._verifyTrailingWhitespace(endInfo) : null);
    };
    /**
     * @param {!Array} counterPair
     * @return {?}
     */
    I2of5Reader.prototype._decodePair = function(counterPair) {
      var i;
      var code;
      /** @type {!Array} */
      var codes = [];
      var self = this;
      /** @type {number} */
      i = 0;
      for (; i < counterPair.length; i++) {
        if (!(code = self._decodeCode(counterPair[i]))) {
          return null;
        }
        codes.push(code);
      }
      return codes;
    };
    /**
     * @param {!Array} counter
     * @return {?}
     */
    I2of5Reader.prototype._decodeCode = function(counter) {
      var i;
      var error;
      var code;
      var self = this;
      /** @type {number} */
      var sum = 0;
      var epsilon = self.AVG_CODE_ERROR;
      var bestMatch = {
        error : Number.MAX_VALUE,
        code : -1,
        start : 0,
        end : 0
      };
      /** @type {number} */
      i = 0;
      for (; i < counter.length; i++) {
        sum = sum + counter[i];
      }
      /** @type {number} */
      code = 0;
      for (; code < self.CODE_PATTERN.length; code++) {
        if ((error = self._matchPattern(counter, self.CODE_PATTERN[code])) < bestMatch.error) {
          /** @type {number} */
          bestMatch.code = code;
          bestMatch.error = error;
        }
      }
      if (bestMatch.error < epsilon) {
        return bestMatch;
      }
    };
    /**
     * @param {!Object} counters
     * @param {!Array} result
     * @param {!Array} decodedCodes
     * @return {?}
     */
    I2of5Reader.prototype._decodePayload = function(counters, result, decodedCodes) {
      var i;
      var codes;
      var self = this;
      /** @type {number} */
      var pos = 0;
      var counterLength = counters.length;
      /** @type {!Array} */
      var counterPair = [[0, 0, 0, 0, 0], [0, 0, 0, 0, 0]];
      for (; pos < counterLength;) {
        /** @type {number} */
        i = 0;
        for (; i < 5; i++) {
          /** @type {number} */
          counterPair[0][i] = counters[pos] * this.barSpaceRatio[0];
          /** @type {number} */
          counterPair[1][i] = counters[pos + 1] * this.barSpaceRatio[1];
          /** @type {number} */
          pos = pos + 2;
        }
        if (!(codes = self._decodePair(counterPair))) {
          return null;
        }
        /** @type {number} */
        i = 0;
        for (; i < codes.length; i++) {
          result.push(codes[i].code + "");
          decodedCodes.push(codes[i]);
        }
      }
      return codes;
    };
    /**
     * @param {number} counters
     * @return {?}
     */
    I2of5Reader.prototype._verifyCounterLength = function(counters) {
      return counters.length % 10 == 0;
    };
    /**
     * @return {?}
     */
    I2of5Reader.prototype._decode = function() {
      var startInfo;
      var endInfo;
      var counters;
      var self = this;
      /** @type {!Array} */
      var result = [];
      /** @type {!Array} */
      var decodedCodes = [];
      return (startInfo = self._findStart()) ? (decodedCodes.push(startInfo), (endInfo = self._findEnd()) ? (counters = self._fillCounters(startInfo.end, endInfo.start, false), self._verifyCounterLength(counters) && self._decodePayload(counters, result, decodedCodes) ? result.length % 2 != 0 || result.length < 6 ? null : (decodedCodes.push(endInfo), {
        code : result.join(""),
        start : startInfo.start,
        end : endInfo.end,
        startInfo : startInfo,
        decodedCodes : decodedCodes
      }) : null) : null) : null;
    };
    I2of5Reader.CONFIG_KEYS = {
      normalizeBarSpaceWidth : {
        type : "boolean",
        default : false,
        description : "If true, the reader tries to normalize thewidth-difference between bars and spaces"
      }
    };
    /** @type {function(?): undefined} */
    defaultTagAttributes.a = I2of5Reader;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @param {?} opts
     * @param {?} supplements
     * @return {undefined}
     */
    function UPCEReader(opts, supplements) {
      o.a.call(this, opts, supplements);
    }
    var o = keyGen(4);
    var properties = {
      CODE_FREQUENCY : {
        value : [[56, 52, 50, 49, 44, 38, 35, 42, 41, 37], [7, 11, 13, 14, 19, 25, 28, 21, 22, 26]]
      },
      STOP_PATTERN : {
        value : [1 / 6 * 7, 1 / 6 * 7, 1 / 6 * 7, 1 / 6 * 7, 1 / 6 * 7, 1 / 6 * 7]
      },
      FORMAT : {
        value : "upc_e",
        writeable : false
      }
    };
    /** @type {!Object} */
    UPCEReader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?, ?): undefined} */
    UPCEReader.prototype.constructor = UPCEReader;
    /**
     * @param {!Object} code
     * @param {!Array} result
     * @param {!Array} decodedCodes
     * @return {?}
     */
    UPCEReader.prototype._decodePayload = function(code, result, decodedCodes) {
      var r;
      var self = this;
      /** @type {number} */
      var codeFrequency = 0;
      /** @type {number} */
      r = 0;
      for (; r < 6; r++) {
        if (!(code = self._decodeCode(code.end))) {
          return null;
        }
        if (code.code >= self.CODE_G_START) {
          /** @type {number} */
          code.code = code.code - self.CODE_G_START;
          /** @type {number} */
          codeFrequency = codeFrequency | 1 << 5 - r;
        }
        result.push(code.code);
        decodedCodes.push(code);
      }
      return self._determineParity(codeFrequency, result) ? code : null;
    };
    /**
     * @param {number} codeFrequency
     * @param {!Array} result
     * @return {?}
     */
    UPCEReader.prototype._determineParity = function(codeFrequency, result) {
      var i;
      var nrSystem;
      /** @type {number} */
      nrSystem = 0;
      for (; nrSystem < this.CODE_FREQUENCY.length; nrSystem++) {
        /** @type {number} */
        i = 0;
        for (; i < this.CODE_FREQUENCY[nrSystem].length; i++) {
          if (codeFrequency === this.CODE_FREQUENCY[nrSystem][i]) {
            return result.unshift(nrSystem), result.push(i), true;
          }
        }
      }
      return false;
    };
    /**
     * @param {!Array} result
     * @return {?}
     */
    UPCEReader.prototype._convertToUPCA = function(result) {
      /** @type {!Array} */
      var sharedResources = [result[0]];
      var lastDigit = result[result.length - 2];
      return sharedResources = lastDigit <= 2 ? sharedResources.concat(result.slice(1, 3)).concat([lastDigit, 0, 0, 0, 0]).concat(result.slice(3, 6)) : 3 === lastDigit ? sharedResources.concat(result.slice(1, 4)).concat([0, 0, 0, 0, 0]).concat(result.slice(4, 6)) : 4 === lastDigit ? sharedResources.concat(result.slice(1, 5)).concat([0, 0, 0, 0, 0, result[5]]) : sharedResources.concat(result.slice(1, 6)).concat([0, 0, 0, 0, lastDigit]), sharedResources.push(result[result.length - 1]), sharedResources;
    };
    /**
     * @param {!Array} result
     * @return {?}
     */
    UPCEReader.prototype._checksum = function(result) {
      return o.a.prototype._checksum.call(this, this._convertToUPCA(result));
    };
    /**
     * @param {?} fn
     * @param {boolean} opts
     * @return {?}
     */
    UPCEReader.prototype._findEnd = function(fn, opts) {
      return opts = true, o.a.prototype._findEnd.call(this, fn, opts);
    };
    /**
     * @param {!Object} endInfo
     * @return {?}
     */
    UPCEReader.prototype._verifyTrailingWhitespace = function(endInfo) {
      var patternStart;
      var self = this;
      if ((patternStart = endInfo.end + (endInfo.end - endInfo.start) / 2) < self._row.length && self._matchRange(endInfo.end, patternStart, 0)) {
        return endInfo;
      }
    };
    /** @type {function(?, ?): undefined} */
    defaultTagAttributes.a = UPCEReader;
  }, function(canCreateDiscussions, defaultTagAttributes, keyGen) {
    /**
     * @param {?} opts
     * @param {?} rwd
     * @return {undefined}
     */
    function I2of5Reader(opts, rwd) {
      o.a.call(this, opts, rwd);
    }
    var o = keyGen(4);
    var properties = {
      FORMAT : {
        value : "upc_a",
        writeable : false
      }
    };
    /** @type {!Object} */
    I2of5Reader.prototype = Object.create(o.a.prototype, properties);
    /** @type {function(?, ?): undefined} */
    I2of5Reader.prototype.constructor = I2of5Reader;
    /**
     * @return {?}
     */
    I2of5Reader.prototype._decode = function() {
      var query = o.a.prototype._decode.call(this);
      return query && query.code && 13 === query.code.length && "0" === query.code.charAt(0) ? (query.code = query.code.substring(1), query) : null;
    };
    /** @type {function(?, ?): undefined} */
    defaultTagAttributes.a = I2of5Reader;
  }, function(mixin, canCreateDiscussions) {
    /**
     * @param {!Array} targetMethod
     * @param {!Object} transformPromise
     * @return {?}
     */
    function createTransformedConnection(targetMethod, transformPromise) {
      return targetMethod[0] = transformPromise[0], targetMethod[1] = transformPromise[1], targetMethod[2] = transformPromise[2], targetMethod[3] = transformPromise[3], targetMethod;
    }
    /** @type {function(!Array, !Object): ?} */
    mixin.exports = createTransformedConnection;
  }, function(module, canCreateDiscussions) {
    /**
     * @return {?}
     */
    function LightShader() {
      /** @type {!Float32Array} */
      var temp2v4_ = new Float32Array(4);
      return temp2v4_[0] = 1, temp2v4_[1] = 0, temp2v4_[2] = 0, temp2v4_[3] = 1, temp2v4_;
    }
    /** @type {function(): ?} */
    module.exports = LightShader;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Array} a
     * @param {!Object} b
     * @return {?}
     */
    function apply(a, b) {
      var d = b[0];
      var g = b[1];
      var e = b[2];
      var h = b[3];
      /** @type {number} */
      var f = d * h - e * g;
      return f ? (f = 1 / f, a[0] = h * f, a[1] = -g * f, a[2] = -e * f, a[3] = d * f, a) : null;
    }
    /** @type {function(!Array, !Object): ?} */
    module.exports = apply;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Object} data
     * @param {number} o
     * @param {!Array} n
     * @return {?}
     */
    function n(data, o, n) {
      return data[0] = o[0] * n, data[1] = o[1] * n, data;
    }
    /** @type {function(!Object, number, !Array): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Object} options
     * @param {number} data
     * @param {!Array} matrix
     * @return {?}
     */
    function sightglass(options, data, matrix) {
      var r = data[0];
      var g = data[1];
      return options[0] = matrix[0] * r + matrix[2] * g, options[1] = matrix[1] * r + matrix[3] * g, options;
    }
    /** @type {function(!Object, number, !Array): ?} */
    module.exports = sightglass;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Array} b
     * @return {?}
     */
    function BinaryBundle(b) {
      /** @type {!Float32Array} */
      var a = new Float32Array(3);
      return a[0] = b[0], a[1] = b[1], a[2] = b[2], a;
    }
    /** @type {function(!Array): ?} */
    module.exports = BinaryBundle;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {string} o
     * @return {undefined}
     */
    function self(o) {
      /** @type {number} */
      var j = -1;
      var r_len = null == o ? 0 : o.length;
      this.clear();
      for (; ++j < r_len;) {
        var a = o[j];
        this.set(a[0], a[1]);
      }
    }
    var listCacheClear = __webpack_require__(122);
    var method = __webpack_require__(123);
    var hashGet = __webpack_require__(124);
    var has = __webpack_require__(125);
    var cookie = __webpack_require__(126);
    self.prototype.clear = listCacheClear;
    self.prototype.delete = method;
    self.prototype.get = hashGet;
    self.prototype.has = has;
    self.prototype.set = cookie;
    /** @type {function(string): undefined} */
    module.exports = self;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {?} name
     * @return {undefined}
     */
    function Stack(name) {
      var data = this.__data__ = new Context(name);
      this.size = data.size;
    }
    var Context = require(10);
    var clear = require(149);
    var del = require(150);
    var action = require(151);
    var has = require(152);
    var cookie = require(153);
    Stack.prototype.clear = clear;
    Stack.prototype.delete = del;
    Stack.prototype.get = action;
    Stack.prototype.has = has;
    Stack.prototype.set = cookie;
    /** @type {function(?): undefined} */
    module.exports = Stack;
  }, function(module, canCreateDiscussions, defaultValue) {
    var view = defaultValue(5);
    var Uint8Array = view.Uint8Array;
    module.exports = Uint8Array;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Function} m
     * @param {number} n
     * @param {!Object} s
     * @return {?}
     */
    function n(m, n, s) {
      switch(s.length) {
        case 0:
          return m.call(n);
        case 1:
          return m.call(n, s[0]);
        case 2:
          return m.call(n, s[0], s[1]);
        case 3:
          return m.call(n, s[0], s[1], s[2]);
      }
      return m.apply(n, s);
    }
    /** @type {function(!Function, number, !Object): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!NodeList} value
     * @param {number} inherited
     * @return {?}
     */
    function arrayLikeKeys(value, inherited) {
      var samePriority = isBuffer(value);
      var lowerPriority = !samePriority && isArray(value);
      var oAttributeValue = !samePriority && !lowerPriority && isTypedArray(value);
      var isType = !samePriority && !lowerPriority && !oAttributeValue && isArguments(value);
      var skipIndexes = samePriority || lowerPriority || oAttributeValue || isType;
      var result = skipIndexes ? baseTimes(value.length, String) : [];
      var exists = result.length;
      var undefined;
      for (undefined in value) {
        if (!(!inherited && !hasOwnProperty.call(value, undefined) || skipIndexes && ("length" == undefined || oAttributeValue && ("offset" == undefined || "parent" == undefined) || isType && ("buffer" == undefined || "byteLength" == undefined || "byteOffset" == undefined) || c(undefined, exists)))) {
          result.push(undefined);
        }
      }
      return result;
    }
    var baseTimes = __webpack_require__(107);
    var isArray = __webpack_require__(18);
    var isBuffer = __webpack_require__(2);
    var isTypedArray = __webpack_require__(44);
    var c = __webpack_require__(15);
    var isArguments = __webpack_require__(45);
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {function(!NodeList, number): ?} */
    module.exports = arrayLikeKeys;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {string} c
     * @param {number} callback
     * @return {?}
     */
    function on(c, callback) {
      /** @type {number} */
      var i = -1;
      var length = null == c ? 0 : c.length;
      /** @type {!Array} */
      var result = Array(length);
      for (; ++i < length;) {
        result[i] = callback(c[i], i, c);
      }
      return result;
    }
    /** @type {function(string, number): ?} */
    module.exports = on;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!NodeList} el
     * @param {number} events
     * @return {?}
     */
    function on(el, events) {
      /** @type {number} */
      var i = -1;
      var len = events.length;
      var offset = el.length;
      for (; ++i < len;) {
        el[offset + i] = events[i];
      }
      return el;
    }
    /** @type {function(!NodeList, number): ?} */
    module.exports = on;
  }, function(module, canCreateDiscussions, iter_f) {
    var next = iter_f(0);
    /** @type {function((Object|null), (Object|null)=): !Object} */
    var create = Object.create;
    var storeMixin = function() {
      /**
       * @return {undefined}
       */
      function Connection() {
      }
      return function(self) {
        if (!next(self)) {
          return {};
        }
        if (create) {
          return create(self);
        }
        /** @type {!Object} */
        Connection.prototype = self;
        var connection = new Connection;
        return Connection.prototype = void 0, connection;
      };
    }();
    module.exports = storeMixin;
  }, function(task, canCreateDiscussions, getPoint) {
    /**
     * @param {!NodeList} e
     * @param {number} array
     * @param {!Array} p
     * @param {string} d
     * @param {!Array} t
     * @return {?}
     */
    function r(e, array, p, d, t) {
      /** @type {number} */
      var i = -1;
      var index = e.length;
      if (!p) {
        p = end;
      }
      if (!t) {
        /** @type {!Array} */
        t = [];
      }
      for (; ++i < index;) {
        var a = e[i];
        if (array > 0 && p(a)) {
          if (array > 1) {
            r(a, array - 1, p, d, t);
          } else {
            p(t, a);
          }
        } else {
          if (!d) {
            t[t.length] = a;
          }
        }
      }
      return t;
    }
    var p = getPoint(90);
    var end = getPoint(128);
    /** @type {function(!NodeList, number, !Array, string, !Array): ?} */
    task.exports = r;
  }, function(mixin, canCreateDiscussions, $parse) {
    var mainCheck = $parse(117);
    var m = mainCheck();
    mixin.exports = m;
  }, function(task, canCreateDiscussions, createElement) {
    /**
     * @param {!Object} e
     * @param {number} s
     * @return {?}
     */
    function r(e, s) {
      s = o(s, e);
      /** @type {number} */
      var historyInstance = 0;
      var feedbackId = s.length;
      for (; null != e && historyInstance < feedbackId;) {
        e = e[p(s[historyInstance++])];
      }
      return historyInstance && historyInstance == feedbackId ? e : void 0;
    }
    var o = createElement(13);
    var p = createElement(23);
    /** @type {function(!Object, number): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} o
     * @param {number} value
     * @return {?}
     */
    function n(o, value) {
      return null != o && value in Object(o);
    }
    /** @type {function(?, number): ?} */
    module.exports = n;
  }, function(mixin, canCreateDiscussions, $interval) {
    /**
     * @param {?} i
     * @return {?}
     */
    function m(i) {
      return unsubscribe(i) && _typeof(i) == object;
    }
    var _typeof = $interval(8);
    var unsubscribe = $interval(6);
    /** @type {string} */
    var object = "[object Arguments]";
    /** @type {function(?): ?} */
    mixin.exports = m;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} value
     * @return {?}
     */
    function self(value) {
      return !(!isRegExp(value) || getPrototypeOf(value)) && (isFunction(value) ? reIsNative : reIsHostCtor).test(normalize(value));
    }
    var isFunction = __webpack_require__(25);
    var getPrototypeOf = __webpack_require__(132);
    var isRegExp = __webpack_require__(0);
    var normalize = __webpack_require__(155);
    /** @type {!RegExp} */
    var savedRegExp = /[\\^$.*+?()[\]{}|]/g;
    /** @type {!RegExp} */
    var reIsHostCtor = /^\[object .+?Constructor\]$/;
    var funcProto = Function.prototype;
    var ObjProto = Object.prototype;
    /** @type {function(this:!Function): string} */
    var funcToString = funcProto.toString;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {!RegExp} */
    var reIsNative = RegExp("^" + funcToString.call(hasOwnProperty).replace(savedRegExp, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$");
    /** @type {function(?): ?} */
    module.exports = self;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {!NodeList} x
     * @return {?}
     */
    function r(x) {
      return isArray(x) && isNumber(x.length) && !!row[m(x)];
    }
    var m = require(8);
    var isNumber = require(26);
    var isArray = require(6);
    var row = {};
    /** @type {boolean} */
    row["[object Float32Array]"] = row["[object Float64Array]"] = row["[object Int8Array]"] = row["[object Int16Array]"] = row["[object Int32Array]"] = row["[object Uint8Array]"] = row["[object Uint8ClampedArray]"] = row["[object Uint16Array]"] = row["[object Uint32Array]"] = true;
    /** @type {boolean} */
    row["[object Arguments]"] = row["[object Array]"] = row["[object ArrayBuffer]"] = row["[object Boolean]"] = row["[object DataView]"] = row["[object Date]"] = row["[object Error]"] = row["[object Function]"] = row["[object Map]"] = row["[object Number]"] = row["[object Object]"] = row["[object RegExp]"] = row["[object Set]"] = row["[object String]"] = row["[object WeakMap]"] = false;
    /** @type {function(!NodeList): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions, n) {
    /**
     * @param {?} value
     * @return {?}
     */
    function f(value) {
      if (!h(value)) {
        return c(value);
      }
      var done = next(value);
      /** @type {!Array} */
      var _results = [];
      var name;
      for (name in value) {
        if ("constructor" != name || !done && hasOwnProperty.call(value, name)) {
          _results.push(name);
        }
      }
      return _results;
    }
    var h = n(0);
    var next = n(40);
    var c = n(144);
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {function(?): ?} */
    module.exports = f;
  }, function(task, canCreateDiscussions, n) {
    /**
     * @param {number} i
     * @param {number} n
     * @param {!Array} d
     * @param {string} a
     * @param {!Array} l
     * @return {undefined}
     */
    function r(i, n, d, a, l) {
      if (i !== n) {
        f(n, function(v, c) {
          if (o(v)) {
            if (!l) {
              l = new GamekitLayer;
            }
            u(i, n, c, d, r, a, l);
          } else {
            var u = a ? a(i[c], v, c + "", i, n, l) : void 0;
            if (void 0 === u) {
              /** @type {!Object} */
              u = v;
            }
            h(i, c, u);
          }
        }, i);
      }
    }
    var GamekitLayer = n(85);
    var h = n(35);
    var f = n(93);
    var u = n(101);
    var o = n(0);
    var i = n(46);
    /** @type {function(number, number, !Array, string, !Array): undefined} */
    task.exports = r;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {!NodeList} keys
     * @param {number} items
     * @param {number} k
     * @param {string} name
     * @param {!Array} debug
     * @param {string} callback
     * @param {!Object} options
     * @return {?}
     */
    function validate(keys, items, k, name, debug, callback, options) {
      var key = keys[k];
      var value = items[k];
      var err = options.get(value);
      if (err) {
        return void get(keys, k, err);
      }
      var result = callback ? callback(key, value, k + "", keys, items, options) : void 0;
      /** @type {boolean} */
      var flag = void 0 === result;
      if (flag) {
        var h = parse(value);
        var m = !h && runStack(value);
        var s = !h && !m && _(value);
        result = value;
        if (h || m || s) {
          if (parse(key)) {
            result = key;
          } else {
            if (getCommentTag(key)) {
              result = p(key);
            } else {
              if (m) {
                /** @type {boolean} */
                flag = false;
                result = c(value, true);
              } else {
                if (s) {
                  /** @type {boolean} */
                  flag = false;
                  result = validateNonBulkDestructive(value, true);
                } else {
                  /** @type {!Array} */
                  result = [];
                }
              }
            }
          }
        } else {
          if (isBlank(value) || validator(value)) {
            result = key;
            if (validator(key)) {
              result = getDocumentCollection(key);
            } else {
              if (!normalRemove(key) || name && startsWith(key)) {
                result = displayStateStr(value);
              }
            }
          } else {
            /** @type {boolean} */
            flag = false;
          }
        }
      }
      if (flag) {
        options.set(value, result);
        debug(result, value, name, callback, options);
        options.delete(value);
      }
      get(keys, k, result);
    }
    var get = require(35);
    var c = require(111);
    var validateNonBulkDestructive = require(112);
    var p = require(113);
    var displayStateStr = require(127);
    var validator = require(18);
    var parse = require(2);
    var getCommentTag = require(159);
    var runStack = require(44);
    var startsWith = require(25);
    var normalRemove = require(0);
    var isBlank = require(160);
    var _ = require(45);
    var getDocumentCollection = require(164);
    /** @type {function(!NodeList, number, number, string, !Array, string, !Object): ?} */
    module.exports = validate;
  }, function(module, canCreateDiscussions, n) {
    /**
     * @param {?} b
     * @param {number} profile
     * @return {?}
     */
    function BinaryBundle(b, profile) {
      return next(b, profile, function(canCreateDiscussions, tick) {
        return end(b, tick);
      });
    }
    var next = n(103);
    var end = n(158);
    /** @type {function(?, number): ?} */
    module.exports = BinaryBundle;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {?} b
     * @param {number} result
     * @param {!Array} value
     * @return {?}
     */
    function exports(b, result, value) {
      /** @type {number} */
      var i = -1;
      var index = result.length;
      var c = {};
      for (; ++i < index;) {
        var name = result[i];
        var f = _(b, name);
        if (value(f, name)) {
          add(c, h(name, b), f);
        }
      }
      return c;
    }
    var _ = require(94);
    var add = require(105);
    var h = require(13);
    /** @type {function(?, number, !Array): ?} */
    module.exports = exports;
  }, function(task, canCreateDiscussions, integrate) {
    /**
     * @param {string} x
     * @param {number} r
     * @return {?}
     */
    function r(x, r) {
      return add(callback(x, r, off), x + "");
    }
    var off = integrate(43);
    var callback = integrate(41);
    var add = integrate(42);
    /** @type {function(string, number): ?} */
    task.exports = r;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {!Object} key
     * @param {number} node
     * @param {!Object} index
     * @param {string} fn
     * @return {?}
     */
    function r(key, node, index, fn) {
      if (!isArray(key)) {
        return key;
      }
      node = traverse(node, key);
      /** @type {number} */
      var name = -1;
      var pos = node.length;
      /** @type {number} */
      var i = pos - 1;
      /** @type {!Object} */
      var value = key;
      for (; null != value && ++name < pos;) {
        var action = each(node[name]);
        /** @type {!Object} */
        var res = index;
        if (name != i) {
          var val = value[action];
          res = fn ? fn(val, action, value) : void 0;
          if (void 0 === res) {
            res = isArray(val) ? val : render(node[name + 1]) ? [] : {};
          }
        }
        find(value, action, res);
        value = value[action];
      }
      return key;
    }
    var find = require(36);
    var traverse = require(13);
    var render = require(15);
    var isArray = require(0);
    var each = require(23);
    /** @type {function(!Object, number, !Object, string): ?} */
    task.exports = r;
  }, function(mixin, canCreateDiscussions, __webpack_require__) {
    var getValue = __webpack_require__(156);
    var defineProperty = __webpack_require__(37);
    var tmp = __webpack_require__(43);
    var m = defineProperty ? function(func, originalJSONGraph) {
      return defineProperty(func, "toString", {
        configurable : true,
        enumerable : false,
        value : getValue(originalJSONGraph),
        writable : true
      });
    } : tmp;
    mixin.exports = m;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {number} n
     * @param {number} fn
     * @return {?}
     */
    function n(n, fn) {
      /** @type {number} */
      var i = -1;
      /** @type {!Array} */
      var result = Array(n);
      for (; ++i < n;) {
        result[i] = fn(i);
      }
      return result;
    }
    /** @type {function(number, number): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {number} key
     * @return {?}
     */
    function h(key) {
      if ("string" == typeof key) {
        return key;
      }
      if (isString(key)) {
        return a(key, h) + "";
      }
      if (invariant(key)) {
        return valid ? valid.call(key) : "";
      }
      /** @type {string} */
      var keyString = key + "";
      return "0" == keyString && 1 / key == -Infinity ? "-0" : keyString;
    }
    var Symbol = require(11);
    var a = require(89);
    var isString = require(2);
    var invariant = require(27);
    /** @type {number} */
    var Infinity = 1 / 0;
    var symbolProto = Symbol ? Symbol.prototype : void 0;
    var valid = symbolProto ? symbolProto.toString : void 0;
    /** @type {function(number): ?} */
    module.exports = h;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} b
     * @return {?}
     */
    function all(b) {
      return function(applyBackgroundUpdates) {
        return b(applyBackgroundUpdates);
      };
    }
    /** @type {function(?): ?} */
    module.exports = all;
  }, function(context, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!Object} arrayBuffer
     * @return {?}
     */
    function init(arrayBuffer) {
      var lump = new arrayBuffer.constructor(arrayBuffer.byteLength);
      return (new Uint8Array(lump)).set(new Uint8Array(arrayBuffer)), lump;
    }
    var Uint8Array = __webpack_require__(86);
    /** @type {function(!Object): ?} */
    context.exports = init;
  }, function(i, exports, interpret) {
    (function(module) {
      /**
       * @param {!Object} array
       * @param {number} errorHandler
       * @return {?}
       */
      function init(array, errorHandler) {
        if (errorHandler) {
          return array.slice();
        }
        var length = array.length;
        var result = allocUnsafe ? allocUnsafe(length) : new array.constructor(length);
        return array.copy(result), result;
      }
      var root = interpret(5);
      var freeExports = "object" == typeof exports && exports && !exports.nodeType && exports;
      var freeModule = freeExports && "object" == typeof module && module && !module.nodeType && module;
      var moduleExports = freeModule && freeModule.exports === freeExports;
      var Buffer = moduleExports ? root.Buffer : void 0;
      var allocUnsafe = Buffer ? Buffer.allocUnsafe : void 0;
      /** @type {function(!Object, number): ?} */
      module.exports = init;
    }).call(exports, interpret(29)(i));
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!Object} typedArray
     * @param {number} isDeep
     * @return {?}
     */
    function cloneTypedArray(typedArray, isDeep) {
      var buffer = isDeep ? cloneArrayBuffer(typedArray.buffer) : typedArray.buffer;
      return new typedArray.constructor(buffer, typedArray.byteOffset, typedArray.length);
    }
    var cloneArrayBuffer = __webpack_require__(110);
    /** @type {function(!Object, number): ?} */
    module.exports = cloneTypedArray;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!NodeList} object
     * @param {number} array
     * @return {?}
     */
    function on(object, array) {
      /** @type {number} */
      var i = -1;
      var length = object.length;
      if (!array) {
        /** @type {!Array} */
        array = Array(length);
      }
      for (; ++i < length;) {
        array[i] = object[i];
      }
      return array;
    }
    /** @type {function(!NodeList, number): ?} */
    module.exports = on;
  }, function(module, canCreateDiscussions, n) {
    /**
     * @param {?} match
     * @param {number} counter
     * @param {!Object} value
     * @param {string} format
     * @return {?}
     */
    function exports(match, counter, value, format) {
      /** @type {boolean} */
      var updateValueOnFirstWatch = !value;
      if (!value) {
        value = {};
      }
      /** @type {number} */
      var n = -1;
      var max = counter.length;
      for (; ++n < max;) {
        var i = counter[n];
        var key = format ? format(value[i], match[i], i, value, match) : void 0;
        if (void 0 === key) {
          key = match[i];
        }
        if (updateValueOnFirstWatch) {
          h(value, i, key);
        } else {
          f(value, i, key);
        }
      }
      return value;
    }
    var f = n(36);
    var h = n(21);
    /** @type {function(?, number, !Object, string): ?} */
    module.exports = exports;
  }, function(module, canCreateDiscussions, interpret) {
    var root = interpret(5);
    var coreJsData = root["__core-js_shared__"];
    module.exports = coreJsData;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!NodeList} assigner
     * @return {?}
     */
    function createAssigner(assigner) {
      return baseRest(function(object, sources) {
        /** @type {number} */
        var index = -1;
        var length = sources.length;
        var customizer = length > 1 ? sources[length - 1] : void 0;
        var thisArg = length > 2 ? sources[2] : void 0;
        /** @type {(!Function|undefined)} */
        customizer = assigner.length > 3 && "function" == typeof customizer ? (length--, customizer) : void 0;
        if (thisArg && isIterateeCall(sources[0], sources[1], thisArg)) {
          /** @type {(!Function|undefined)} */
          customizer = length < 3 ? void 0 : customizer;
          /** @type {number} */
          length = 1;
        }
        /** @type {!Object} */
        object = Object(object);
        for (; ++index < length;) {
          var source = sources[index];
          if (source) {
            assigner(object, source, index, customizer);
          }
        }
        return object;
      });
    }
    var baseRest = __webpack_require__(104);
    var isIterateeCall = __webpack_require__(129);
    /** @type {function(!NodeList): ?} */
    module.exports = createAssigner;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {number} index
     * @return {?}
     */
    function createAssigner(index) {
      return function(obj, traverse, keysFunc) {
        /** @type {number} */
        var i = -1;
        /** @type {!Object} */
        var params = Object(obj);
        var result = keysFunc(obj);
        var y = result.length;
        for (; y--;) {
          var key = result[index ? y : ++i];
          if (traverse(params[key], key, params) === false) {
            break;
          }
        }
        return obj;
      };
    }
    /** @type {function(number): ?} */
    module.exports = createAssigner;
  }, function(task, canCreateDiscussions, integrate) {
    /**
     * @param {string} val
     * @return {?}
     */
    function r(val) {
      return add(on(val, void 0, one), val + "");
    }
    var one = integrate(157);
    var on = integrate(41);
    var add = integrate(42);
    /** @type {function(string): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} item
     * @return {?}
     */
    function ColorReplaceFilter(item) {
      /** @type {boolean} */
      var groupKey = hasOwnProp.call(item, key);
      var index = item[key];
      try {
        item[key] = void 0;
        /** @type {boolean} */
        var e = true;
      } catch (t) {
      }
      /** @type {string} */
      var t = toString.call(item);
      return e && (groupKey ? item[key] = index : delete item[key]), t;
    }
    var Symbol = __webpack_require__(11);
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProp = ObjProto.hasOwnProperty;
    /** @type {function(this:*): string} */
    var toString = ObjProto.toString;
    var key = Symbol ? Symbol.toStringTag : void 0;
    /** @type {function(?): ?} */
    module.exports = ColorReplaceFilter;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {string} object
     * @param {number} name
     * @return {?}
     */
    function on(object, name) {
      return null == object ? void 0 : object[name];
    }
    /** @type {function(string, number): ?} */
    module.exports = on;
  }, function(asset, canCreateDiscussions, require) {
    /**
     * @param {string} elem
     * @param {number} e
     * @param {!Array} f
     * @return {?}
     */
    function i(elem, e, f) {
      e = f(e, elem);
      /** @type {number} */
      var i = -1;
      var l = e.length;
      /** @type {boolean} */
      var isRNA = false;
      for (; ++i < l;) {
        var name = $(e[i]);
        if (!(isRNA = null != elem && f(elem, name))) {
          break;
        }
        elem = elem[name];
      }
      return isRNA || ++i != l ? isRNA : !!(l = null == elem ? 0 : elem.length) && op(l) && format(name, l) && (a(elem) || b(elem));
    }
    var f = require(13);
    var b = require(18);
    var a = require(2);
    var format = require(15);
    var op = require(26);
    var $ = require(23);
    /** @type {function(string, number, !Array): ?} */
    asset.exports = i;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @return {undefined}
     */
    function hashClear() {
      this.__data__ = nativeCreate ? nativeCreate(null) : {};
      /** @type {number} */
      this.size = 0;
    }
    var nativeCreate = __webpack_require__(16);
    /** @type {function(): undefined} */
    module.exports = hashClear;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} name
     * @return {?}
     */
    function bind(name) {
      var result = this.has(name) && delete this.__data__[name];
      return this.size -= result ? 1 : 0, result;
    }
    /** @type {function(?): ?} */
    module.exports = bind;
  }, function(module, canCreateDiscussions, floor) {
    /**
     * @param {?} key
     * @return {?}
     */
    function listCacheDelete(key) {
      var data = this.__data__;
      if (startYNew) {
        var n = data[key];
        return n === trident ? void 0 : n;
      }
      return hasOwnProperty.call(data, key) ? data[key] : void 0;
    }
    var startYNew = floor(16);
    /** @type {string} */
    var trident = "__lodash_hash_undefined__";
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {function(?): ?} */
    module.exports = listCacheDelete;
  }, function(module, canCreateDiscussions, getDefault) {
    /**
     * @param {?} path
     * @return {?}
     */
    function click(path) {
      var value = this.__data__;
      return func ? void 0 !== value[path] : hasOwnProperty.call(value, path);
    }
    var func = getDefault(16);
    var ObjProto = Object.prototype;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {function(?): ?} */
    module.exports = click;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} key
     * @param {number} value
     * @return {?}
     */
    function hashSet(key, value) {
      var data = this.__data__;
      return this.size += this.has(key) ? 0 : 1, data[key] = nativeCreate && void 0 === value ? HASH_UNDEFINED : value, this;
    }
    var nativeCreate = __webpack_require__(16);
    /** @type {string} */
    var HASH_UNDEFINED = "__lodash_hash_undefined__";
    /** @type {function(?, number): ?} */
    module.exports = hashSet;
  }, function(task, canCreateDiscussions, $) {
    /**
     * @param {!Node} file
     * @return {?}
     */
    function cb(file) {
      return "function" != typeof file.constructor || remove(file) ? {} : expect(t(file));
    }
    var expect = $(91);
    var t = $(39);
    var remove = $(40);
    /** @type {function(!Node): ?} */
    task.exports = cb;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!Object} n
     * @return {?}
     */
    function validate(n) {
      return isNaN(n) || isBetween(n) || !!(c && n && n[c]);
    }
    var Symbol = __webpack_require__(11);
    var isBetween = __webpack_require__(18);
    var isNaN = __webpack_require__(2);
    var c = Symbol ? Symbol.isConcatSpreadable : void 0;
    /** @type {function(!Object): ?} */
    module.exports = validate;
  }, function(task, canCreateDiscussions, require) {
    /**
     * @param {?} a
     * @param {number} b
     * @param {!Array} x
     * @return {?}
     */
    function r(a, b, x) {
      if (!isPromise(x)) {
        return false;
      }
      /** @type {string} */
      var type = typeof b;
      return !!("number" == type ? isArray(x) && isNumber(b, x.length) : "string" == type && b in x) && f(x[b], a);
    }
    var f = require(17);
    var isArray = require(24);
    var isNumber = require(15);
    var isPromise = require(0);
    /** @type {function(?, number, !Array): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {string} key
     * @param {number} object
     * @return {?}
     */
    function isKey(key, object) {
      if (isSymbol(key)) {
        return false;
      }
      /** @type {string} */
      var type = typeof key;
      return !("number" != type && "symbol" != type && "boolean" != type && null != key && !keycode(key)) || (VALID_IDENTIFIER_EXPR.test(key) || !a.test(key) || null != object && key in Object(object));
    }
    var isSymbol = require(2);
    var keycode = require(27);
    /** @type {!RegExp} */
    var a = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/;
    /** @type {!RegExp} */
    var VALID_IDENTIFIER_EXPR = /^\w*$/;
    /** @type {function(string, number): ?} */
    module.exports = isKey;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Object} object
     * @return {?}
     */
    function isKey(object) {
      /** @type {string} */
      var type = typeof object;
      return "string" == type || "number" == type || "symbol" == type || "boolean" == type ? "__proto__" !== object : null === object;
    }
    /** @type {function(!Object): ?} */
    module.exports = isKey;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} options
     * @return {?}
     */
    function CustomComponentType(options) {
      return !!yieldLimit && yieldLimit in options;
    }
    var coreJsData = __webpack_require__(115);
    var yieldLimit = function() {
      /** @type {(Array<string>|null)} */
      var mixElem = /[^.]+$/.exec(coreJsData && coreJsData.keys && coreJsData.keys.IE_PROTO || "");
      return mixElem ? "Symbol(src)_1." + mixElem : "";
    }();
    /** @type {function(?): ?} */
    module.exports = CustomComponentType;
  }, function(module, canCreateDiscussions) {
    /**
     * @return {undefined}
     */
    function Box() {
      /** @type {!Array} */
      this.__data__ = [];
      /** @type {number} */
      this.size = 0;
    }
    /** @type {function(): undefined} */
    module.exports = Box;
  }, function(module, canCreateDiscussions, pointFromEvent) {
    /**
     * @param {?} d
     * @return {?}
     */
    function bind(d) {
      var data = this.__data__;
      var i = p(data, d);
      return !(i < 0) && (i == data.length - 1 ? data.pop() : splice.call(data, i, 1), --this.size, true);
    }
    var p = pointFromEvent(12);
    var array = Array.prototype;
    /** @type {function(this:IArrayLike<T>, *=, *=, ...T): !Array<T>} */
    var splice = array.splice;
    /** @type {function(?): ?} */
    module.exports = bind;
  }, function(module, canCreateDiscussions, iter_f) {
    /**
     * @param {?} i
     * @return {?}
     */
    function click(i) {
      var data = this.__data__;
      var header = next(data, i);
      return header < 0 ? void 0 : data[header][1];
    }
    var next = iter_f(12);
    /** @type {function(?): ?} */
    module.exports = click;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {?} key
     * @return {?}
     */
    function listCacheDelete(key) {
      return assocIndexOf(this.__data__, key) > -1;
    }
    var assocIndexOf = __webpack_require__(12);
    /** @type {function(?): ?} */
    module.exports = listCacheDelete;
  }, function(mixin, canCreateDiscussions, n) {
    /**
     * @param {?} value
     * @param {number} i
     * @return {?}
     */
    function m(value, i) {
      var data = this.__data__;
      var message = f(data, value);
      return message < 0 ? (++this.size, data.push([value, i])) : data[message][1] = i, this;
    }
    var f = n(12);
    /** @type {function(?, number): ?} */
    mixin.exports = m;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @return {undefined}
     */
    function mapCacheClear() {
      /** @type {number} */
      this.size = 0;
      this.__data__ = {
        hash : new Hash,
        map : new (OtherHandlebars || Handlebars),
        string : new Hash
      };
    }
    var Hash = __webpack_require__(84);
    var Handlebars = __webpack_require__(10);
    var OtherHandlebars = __webpack_require__(33);
    /** @type {function(): undefined} */
    module.exports = mapCacheClear;
  }, function(blob, canCreateDiscussions, require) {
    /**
     * @param {?} context
     * @return {?}
     */
    function end(context) {
      var result = $(this, context).delete(context);
      return this.size -= result ? 1 : 0, result;
    }
    var $ = require(14);
    /** @type {function(?): ?} */
    blob.exports = end;
  }, function(pkg, canCreateDiscussions, $) {
    /**
     * @param {undefined} i
     * @return {?}
     */
    function index(i) {
      return find(this, i).get(i);
    }
    var find = $(14);
    /** @type {function(undefined): ?} */
    pkg.exports = index;
  }, function(module, canCreateDiscussions, $parse) {
    /**
     * @param {?} key
     * @return {?}
     */
    function has(key) {
      return get(this, key).has(key);
    }
    var get = $parse(14);
    /** @type {function(?): ?} */
    module.exports = has;
  }, function(c2, canCreateDiscussions, keyGen) {
    /**
     * @param {undefined} b
     * @param {number} e
     * @return {?}
     */
    function add(b, e) {
      var result = o(this, b);
      var n = result.size;
      return result.set(b, e), this.size += result.size == n ? 0 : 1, this;
    }
    var o = keyGen(14);
    /** @type {function(undefined, number): ?} */
    c2.exports = add;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {?} data
     * @return {?}
     */
    function render(data) {
      var result = extend(data, function(canCreateDiscussions) {
        return cache.size === MAX_MEMOIZE_SIZE && cache.clear(), canCreateDiscussions;
      });
      var cache = result.cache;
      return result;
    }
    var extend = require(161);
    /** @type {number} */
    var MAX_MEMOIZE_SIZE = 500;
    /** @type {function(?): ?} */
    module.exports = render;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} arg
     * @return {?}
     */
    function n(arg) {
      /** @type {!Array} */
      var arr = [];
      if (null != arg) {
        var key;
        for (key in Object(arg)) {
          arr.push(key);
        }
      }
      return arr;
    }
    /** @type {function(?): ?} */
    module.exports = n;
  }, function(module, exports, __webpack_require__) {
    (function(module) {
      var freeGlobal = __webpack_require__(38);
      var freeExports = "object" == typeof exports && exports && !exports.nodeType && exports;
      var freeModule = freeExports && "object" == typeof module && module && !module.nodeType && module;
      var moduleExports = freeModule && freeModule.exports === freeExports;
      var freeProcess = moduleExports && freeGlobal.process;
      var BinaryBundle = function() {
        try {
          return freeProcess && freeProcess.binding && freeProcess.binding("util");
        } catch (t) {
        }
      }();
      module.exports = BinaryBundle;
    }).call(exports, __webpack_require__(29)(module));
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} n
     * @return {?}
     */
    function writtenNumber(n) {
      return k.call(n);
    }
    var ObjP = Object.prototype;
    /** @type {function(this:*): string} */
    var k = ObjP.toString;
    /** @type {function(?): ?} */
    module.exports = writtenNumber;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} a
     * @param {number} b
     * @return {?}
     */
    function compose(a, b) {
      return function(args) {
        return a(b(args));
      };
    }
    /** @type {function(?, number): ?} */
    module.exports = compose;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {!Function} callback
     * @return {?}
     */
    function render(callback) {
      /** @type {number} */
      var num_summed = 0;
      /** @type {number} */
      var prevT = 0;
      return function() {
        /** @type {number} */
        var currT = now();
        /** @type {number} */
        var dragstocreate = groupsize - (currT - prevT);
        if (prevT = currT, dragstocreate > 0) {
          if (++num_summed >= summands) {
            return arguments[0];
          }
        } else {
          /** @type {number} */
          num_summed = 0;
        }
        return callback.apply(void 0, arguments);
      };
    }
    /** @type {number} */
    var summands = 800;
    /** @type {number} */
    var groupsize = 16;
    /** @type {function(): number} */
    var now = Date.now;
    /** @type {function(!Function): ?} */
    module.exports = render;
  }, function(module, canCreateDiscussions, keyGen) {
    /**
     * @return {undefined}
     */
    function Box() {
      this.__data__ = new o;
      /** @type {number} */
      this.size = 0;
    }
    var o = keyGen(10);
    /** @type {function(): undefined} */
    module.exports = Box;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} t
     * @return {?}
     */
    function bind(t) {
      var data = this.__data__;
      var textWas = data.delete(t);
      return this.size = data.size, textWas;
    }
    /** @type {function(?): ?} */
    module.exports = bind;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {undefined} key
     * @return {?}
     */
    function stackGet(key) {
      return this.__data__.get(key);
    }
    /** @type {function(undefined): ?} */
    module.exports = stackGet;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} value
     * @return {?}
     */
    function setCacheHas(value) {
      return this.__data__.has(value);
    }
    /** @type {function(?): ?} */
    module.exports = setCacheHas;
  }, function(module, canCreateDiscussions, require) {
    /**
     * @param {undefined} value
     * @param {number} key
     * @return {?}
     */
    function stackSet(value, key) {
      var data = this.__data__;
      if (data instanceof FileResource) {
        var pairs = data.__data__;
        if (!TagHourlyStat || pairs.length < LARGE_ARRAY_SIZE - 1) {
          return pairs.push([value, key]), this.size = ++data.size, this;
        }
        data = this.__data__ = new MapCache(pairs);
      }
      return data.set(value, key), this.size = data.size, this;
    }
    var FileResource = require(10);
    var TagHourlyStat = require(33);
    var MapCache = require(34);
    /** @type {number} */
    var LARGE_ARRAY_SIZE = 200;
    /** @type {function(undefined, number): ?} */
    module.exports = stackSet;
  }, function(mixin, canCreateDiscussions, $parse) {
    var mainCheck = $parse(143);
    /** @type {!RegExp} */
    var r_hasNonAlphanumericCharacter = /^\./;
    /** @type {!RegExp} */
    var formattingRemoveEscapes = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g;
    /** @type {!RegExp} */
    var rcamelCase = /\\(\\)?/g;
    var m = mainCheck(function(token) {
      /** @type {!Array} */
      var buf = [];
      return r_hasNonAlphanumericCharacter.test(token) && buf.push(""), token.replace(formattingRemoveEscapes, function(msg, method, val, termUri) {
        buf.push(val ? termUri.replace(rcamelCase, "$1") : method || msg);
      }), buf;
    });
    mixin.exports = m;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {string} value
     * @return {?}
     */
    function n(value) {
      if (null != value) {
        try {
          return funcToString.call(value);
        } catch (t) {
        }
        try {
          return value + "";
        } catch (t) {
        }
      }
      return "";
    }
    var funcProto = Function.prototype;
    /** @type {function(this:!Function): string} */
    var funcToString = funcProto.toString;
    /** @type {function(string): ?} */
    module.exports = n;
  }, function(module, canCreateDiscussions) {
    /**
     * @param {?} result
     * @return {?}
     */
    function wrap(result) {
      return function() {
        return result;
      };
    }
    /** @type {function(?): ?} */
    module.exports = wrap;
  }, function(module, canCreateDiscussions, bind) {
    /**
     * @param {string} b
     * @return {?}
     */
    function BinaryBundle(b) {
      return (null == b ? 0 : b.length) ? toString(b, 1) : [];
    }
    var toString = bind(92);
    /** @type {function(string): ?} */
    module.exports = BinaryBundle;
  }, function(module, canCreateDiscussions, bind) {
    /**
     * @param {?} obj
     * @param {number} key
     * @return {?}
     */
    function api(obj, key) {
      return null != obj && write(obj, key, cb);
    }
    var cb = bind(95);
    var write = bind(121);
    /** @type {function(?, number): ?} */
    module.exports = api;
  }, function(module, canCreateDiscussions, saveNotifs) {
    /**
     * @param {?} e
     * @return {?}
     */
    function matches(e) {
      return keyMatches(e) && modifierMatches(e);
    }
    var modifierMatches = saveNotifs(24);
    var keyMatches = saveNotifs(6);
    /** @type {function(?): ?} */
    module.exports = matches;
  }, function(task, canCreateDiscussions, n) {
    /**
     * @param {?} o
     * @return {?}
     */
    function r(o) {
      if (!a(o) || i(o) != Null) {
        return false;
      }
      var proto = f(o);
      if (null === proto) {
        return true;
      }
      var Ctor = hasOwnProperty.call(proto, "constructor") && proto.constructor;
      return "function" == typeof Ctor && Ctor instanceof Ctor && funcToString.call(Ctor) == objectCtorString;
    }
    var i = n(8);
    var f = n(39);
    var a = n(6);
    /** @type {string} */
    var Null = "[object Object]";
    var funcProto = Function.prototype;
    var ObjProto = Object.prototype;
    /** @type {function(this:!Function): string} */
    var funcToString = funcProto.toString;
    /** @type {function(this:Object, *): boolean} */
    var hasOwnProperty = ObjProto.hasOwnProperty;
    /** @type {string} */
    var objectCtorString = funcToString.call(Object);
    /** @type {function(?): ?} */
    task.exports = r;
  }, function(module, canCreateDiscussions, __webpack_require__) {
    /**
     * @param {!Function} callback
     * @param {!Function} fn
     * @return {?}
     */
    function memoize(callback, fn) {
      if ("function" != typeof callback || null != fn && "function" != typeof fn) {
        throw new TypeError(ERR_ACCESSORS_NOT_SUPPORTED);
      }
      /**
       * @return {?}
       */
      var memoized = function() {
        /** @type {!Arguments} */
        var value = arguments;
        var i = fn ? fn.apply(this, value) : value[0];
        var cache = memoized.cache;
        if (cache.has(i)) {
          return cache.get(i);
        }
        var result = callback.apply(this, value);
        return memoized.cache = cache.set(i, result) || cache, result;
      };
      return memoized.cache = new (memoize.Cache || MapCache), memoized;
    }
    var MapCache = __webpack_require__(34);
    /** @type {string} */
    var ERR_ACCESSORS_NOT_SUPPORTED = "Expected a function";
    memoize.Cache = MapCache;
    /** @type {function(!Function, !Function): ?} */
    module.exports = memoize;
  }, function(mixin, canCreateDiscussions, $parse) {
    var convertSvgToImageDataSync = $parse(102);
    var mainCheck = $parse(118);
    var m = mainCheck(function(svg, params) {
      return null == svg ? {} : convertSvgToImageDataSync(svg, params);
    });
    mixin.exports = m;
  }, function(module, canCreateDiscussions) {
    /**
     * @return {?}
     */
    function ScrollNavBar() {
      return false;
    }
    /** @type {function(): ?} */
    module.exports = ScrollNavBar;
  }, function(module, canCreateDiscussions, $) {
    /**
     * @param {?} b
     * @return {?}
     */
    function on(b) {
      return check(b, f(b));
    }
    var check = $(114);
    var f = $(46);
    /** @type {function(?): ?} */
    module.exports = on;
  }, function(pkg, canCreateDiscussions, require) {
    /**
     * @param {string} string
     * @return {?}
     */
    function index(string) {
      return null == string ? "" : $(string);
    }
    var $ = require(108);
    /** @type {function(string): ?} */
    pkg.exports = index;
  }, function(module, canCreateDiscussions, factory) {
    module.exports = factory(48);
  }]);
});
