/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/axios/index.js":
/*!*************************************!*\
  !*** ./node_modules/axios/index.js ***!
  \*************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

module.exports = __webpack_require__(/*! ./lib/axios */ "./node_modules/axios/lib/axios.js");

/***/ }),

/***/ "./node_modules/axios/lib/adapters/xhr.js":
/*!************************************************!*\
  !*** ./node_modules/axios/lib/adapters/xhr.js ***!
  \************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");
var settle = __webpack_require__(/*! ./../core/settle */ "./node_modules/axios/lib/core/settle.js");
var cookies = __webpack_require__(/*! ./../helpers/cookies */ "./node_modules/axios/lib/helpers/cookies.js");
var buildURL = __webpack_require__(/*! ./../helpers/buildURL */ "./node_modules/axios/lib/helpers/buildURL.js");
var buildFullPath = __webpack_require__(/*! ../core/buildFullPath */ "./node_modules/axios/lib/core/buildFullPath.js");
var parseHeaders = __webpack_require__(/*! ./../helpers/parseHeaders */ "./node_modules/axios/lib/helpers/parseHeaders.js");
var isURLSameOrigin = __webpack_require__(/*! ./../helpers/isURLSameOrigin */ "./node_modules/axios/lib/helpers/isURLSameOrigin.js");
var createError = __webpack_require__(/*! ../core/createError */ "./node_modules/axios/lib/core/createError.js");
var transitionalDefaults = __webpack_require__(/*! ../defaults/transitional */ "./node_modules/axios/lib/defaults/transitional.js");
var Cancel = __webpack_require__(/*! ../cancel/Cancel */ "./node_modules/axios/lib/cancel/Cancel.js");

module.exports = function xhrAdapter(config) {
  return new Promise(function dispatchXhrRequest(resolve, reject) {
    var requestData = config.data;
    var requestHeaders = config.headers;
    var responseType = config.responseType;
    var onCanceled;
    function done() {
      if (config.cancelToken) {
        config.cancelToken.unsubscribe(onCanceled);
      }

      if (config.signal) {
        config.signal.removeEventListener('abort', onCanceled);
      }
    }

    if (utils.isFormData(requestData)) {
      delete requestHeaders['Content-Type']; // Let the browser set it
    }

    var request = new XMLHttpRequest();

    // HTTP basic authentication
    if (config.auth) {
      var username = config.auth.username || '';
      var password = config.auth.password ? unescape(encodeURIComponent(config.auth.password)) : '';
      requestHeaders.Authorization = 'Basic ' + btoa(username + ':' + password);
    }

    var fullPath = buildFullPath(config.baseURL, config.url);
    request.open(config.method.toUpperCase(), buildURL(fullPath, config.params, config.paramsSerializer), true);

    // Set the request timeout in MS
    request.timeout = config.timeout;

    function onloadend() {
      if (!request) {
        return;
      }
      // Prepare the response
      var responseHeaders = 'getAllResponseHeaders' in request ? parseHeaders(request.getAllResponseHeaders()) : null;
      var responseData = !responseType || responseType === 'text' ||  responseType === 'json' ?
        request.responseText : request.response;
      var response = {
        data: responseData,
        status: request.status,
        statusText: request.statusText,
        headers: responseHeaders,
        config: config,
        request: request
      };

      settle(function _resolve(value) {
        resolve(value);
        done();
      }, function _reject(err) {
        reject(err);
        done();
      }, response);

      // Clean up request
      request = null;
    }

    if ('onloadend' in request) {
      // Use onloadend if available
      request.onloadend = onloadend;
    } else {
      // Listen for ready state to emulate onloadend
      request.onreadystatechange = function handleLoad() {
        if (!request || request.readyState !== 4) {
          return;
        }

        // The request errored out and we didn't get a response, this will be
        // handled by onerror instead
        // With one exception: request that using file: protocol, most browsers
        // will return status as 0 even though it's a successful request
        if (request.status === 0 && !(request.responseURL && request.responseURL.indexOf('file:') === 0)) {
          return;
        }
        // readystate handler is calling before onerror or ontimeout handlers,
        // so we should call onloadend on the next 'tick'
        setTimeout(onloadend);
      };
    }

    // Handle browser request cancellation (as opposed to a manual cancellation)
    request.onabort = function handleAbort() {
      if (!request) {
        return;
      }

      reject(createError('Request aborted', config, 'ECONNABORTED', request));

      // Clean up request
      request = null;
    };

    // Handle low level network errors
    request.onerror = function handleError() {
      // Real errors are hidden from us by the browser
      // onerror should only fire if it's a network error
      reject(createError('Network Error', config, null, request));

      // Clean up request
      request = null;
    };

    // Handle timeout
    request.ontimeout = function handleTimeout() {
      var timeoutErrorMessage = config.timeout ? 'timeout of ' + config.timeout + 'ms exceeded' : 'timeout exceeded';
      var transitional = config.transitional || transitionalDefaults;
      if (config.timeoutErrorMessage) {
        timeoutErrorMessage = config.timeoutErrorMessage;
      }
      reject(createError(
        timeoutErrorMessage,
        config,
        transitional.clarifyTimeoutError ? 'ETIMEDOUT' : 'ECONNABORTED',
        request));

      // Clean up request
      request = null;
    };

    // Add xsrf header
    // This is only done if running in a standard browser environment.
    // Specifically not if we're in a web worker, or react-native.
    if (utils.isStandardBrowserEnv()) {
      // Add xsrf header
      var xsrfValue = (config.withCredentials || isURLSameOrigin(fullPath)) && config.xsrfCookieName ?
        cookies.read(config.xsrfCookieName) :
        undefined;

      if (xsrfValue) {
        requestHeaders[config.xsrfHeaderName] = xsrfValue;
      }
    }

    // Add headers to the request
    if ('setRequestHeader' in request) {
      utils.forEach(requestHeaders, function setRequestHeader(val, key) {
        if (typeof requestData === 'undefined' && key.toLowerCase() === 'content-type') {
          // Remove Content-Type if data is undefined
          delete requestHeaders[key];
        } else {
          // Otherwise add header to the request
          request.setRequestHeader(key, val);
        }
      });
    }

    // Add withCredentials to request if needed
    if (!utils.isUndefined(config.withCredentials)) {
      request.withCredentials = !!config.withCredentials;
    }

    // Add responseType to request if needed
    if (responseType && responseType !== 'json') {
      request.responseType = config.responseType;
    }

    // Handle progress if needed
    if (typeof config.onDownloadProgress === 'function') {
      request.addEventListener('progress', config.onDownloadProgress);
    }

    // Not all browsers support upload events
    if (typeof config.onUploadProgress === 'function' && request.upload) {
      request.upload.addEventListener('progress', config.onUploadProgress);
    }

    if (config.cancelToken || config.signal) {
      // Handle cancellation
      // eslint-disable-next-line func-names
      onCanceled = function(cancel) {
        if (!request) {
          return;
        }
        reject(!cancel || (cancel && cancel.type) ? new Cancel('canceled') : cancel);
        request.abort();
        request = null;
      };

      config.cancelToken && config.cancelToken.subscribe(onCanceled);
      if (config.signal) {
        config.signal.aborted ? onCanceled() : config.signal.addEventListener('abort', onCanceled);
      }
    }

    if (!requestData) {
      requestData = null;
    }

    // Send the request
    request.send(requestData);
  });
};


/***/ }),

/***/ "./node_modules/axios/lib/axios.js":
/*!*****************************************!*\
  !*** ./node_modules/axios/lib/axios.js ***!
  \*****************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./utils */ "./node_modules/axios/lib/utils.js");
var bind = __webpack_require__(/*! ./helpers/bind */ "./node_modules/axios/lib/helpers/bind.js");
var Axios = __webpack_require__(/*! ./core/Axios */ "./node_modules/axios/lib/core/Axios.js");
var mergeConfig = __webpack_require__(/*! ./core/mergeConfig */ "./node_modules/axios/lib/core/mergeConfig.js");
var defaults = __webpack_require__(/*! ./defaults */ "./node_modules/axios/lib/defaults/index.js");

/**
 * Create an instance of Axios
 *
 * @param {Object} defaultConfig The default config for the instance
 * @return {Axios} A new instance of Axios
 */
function createInstance(defaultConfig) {
  var context = new Axios(defaultConfig);
  var instance = bind(Axios.prototype.request, context);

  // Copy axios.prototype to instance
  utils.extend(instance, Axios.prototype, context);

  // Copy context to instance
  utils.extend(instance, context);

  // Factory for creating new instances
  instance.create = function create(instanceConfig) {
    return createInstance(mergeConfig(defaultConfig, instanceConfig));
  };

  return instance;
}

// Create the default instance to be exported
var axios = createInstance(defaults);

// Expose Axios class to allow class inheritance
axios.Axios = Axios;

// Expose Cancel & CancelToken
axios.Cancel = __webpack_require__(/*! ./cancel/Cancel */ "./node_modules/axios/lib/cancel/Cancel.js");
axios.CancelToken = __webpack_require__(/*! ./cancel/CancelToken */ "./node_modules/axios/lib/cancel/CancelToken.js");
axios.isCancel = __webpack_require__(/*! ./cancel/isCancel */ "./node_modules/axios/lib/cancel/isCancel.js");
axios.VERSION = (__webpack_require__(/*! ./env/data */ "./node_modules/axios/lib/env/data.js").version);

// Expose all/spread
axios.all = function all(promises) {
  return Promise.all(promises);
};
axios.spread = __webpack_require__(/*! ./helpers/spread */ "./node_modules/axios/lib/helpers/spread.js");

// Expose isAxiosError
axios.isAxiosError = __webpack_require__(/*! ./helpers/isAxiosError */ "./node_modules/axios/lib/helpers/isAxiosError.js");

module.exports = axios;

// Allow use of default import syntax in TypeScript
module.exports["default"] = axios;


/***/ }),

/***/ "./node_modules/axios/lib/cancel/Cancel.js":
/*!*************************************************!*\
  !*** ./node_modules/axios/lib/cancel/Cancel.js ***!
  \*************************************************/
/***/ ((module) => {

"use strict";


/**
 * A `Cancel` is an object that is thrown when an operation is canceled.
 *
 * @class
 * @param {string=} message The message.
 */
function Cancel(message) {
  this.message = message;
}

Cancel.prototype.toString = function toString() {
  return 'Cancel' + (this.message ? ': ' + this.message : '');
};

Cancel.prototype.__CANCEL__ = true;

module.exports = Cancel;


/***/ }),

/***/ "./node_modules/axios/lib/cancel/CancelToken.js":
/*!******************************************************!*\
  !*** ./node_modules/axios/lib/cancel/CancelToken.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var Cancel = __webpack_require__(/*! ./Cancel */ "./node_modules/axios/lib/cancel/Cancel.js");

/**
 * A `CancelToken` is an object that can be used to request cancellation of an operation.
 *
 * @class
 * @param {Function} executor The executor function.
 */
function CancelToken(executor) {
  if (typeof executor !== 'function') {
    throw new TypeError('executor must be a function.');
  }

  var resolvePromise;

  this.promise = new Promise(function promiseExecutor(resolve) {
    resolvePromise = resolve;
  });

  var token = this;

  // eslint-disable-next-line func-names
  this.promise.then(function(cancel) {
    if (!token._listeners) return;

    var i;
    var l = token._listeners.length;

    for (i = 0; i < l; i++) {
      token._listeners[i](cancel);
    }
    token._listeners = null;
  });

  // eslint-disable-next-line func-names
  this.promise.then = function(onfulfilled) {
    var _resolve;
    // eslint-disable-next-line func-names
    var promise = new Promise(function(resolve) {
      token.subscribe(resolve);
      _resolve = resolve;
    }).then(onfulfilled);

    promise.cancel = function reject() {
      token.unsubscribe(_resolve);
    };

    return promise;
  };

  executor(function cancel(message) {
    if (token.reason) {
      // Cancellation has already been requested
      return;
    }

    token.reason = new Cancel(message);
    resolvePromise(token.reason);
  });
}

/**
 * Throws a `Cancel` if cancellation has been requested.
 */
CancelToken.prototype.throwIfRequested = function throwIfRequested() {
  if (this.reason) {
    throw this.reason;
  }
};

/**
 * Subscribe to the cancel signal
 */

CancelToken.prototype.subscribe = function subscribe(listener) {
  if (this.reason) {
    listener(this.reason);
    return;
  }

  if (this._listeners) {
    this._listeners.push(listener);
  } else {
    this._listeners = [listener];
  }
};

/**
 * Unsubscribe from the cancel signal
 */

CancelToken.prototype.unsubscribe = function unsubscribe(listener) {
  if (!this._listeners) {
    return;
  }
  var index = this._listeners.indexOf(listener);
  if (index !== -1) {
    this._listeners.splice(index, 1);
  }
};

/**
 * Returns an object that contains a new `CancelToken` and a function that, when called,
 * cancels the `CancelToken`.
 */
CancelToken.source = function source() {
  var cancel;
  var token = new CancelToken(function executor(c) {
    cancel = c;
  });
  return {
    token: token,
    cancel: cancel
  };
};

module.exports = CancelToken;


/***/ }),

/***/ "./node_modules/axios/lib/cancel/isCancel.js":
/*!***************************************************!*\
  !*** ./node_modules/axios/lib/cancel/isCancel.js ***!
  \***************************************************/
/***/ ((module) => {

"use strict";


module.exports = function isCancel(value) {
  return !!(value && value.__CANCEL__);
};


/***/ }),

/***/ "./node_modules/axios/lib/core/Axios.js":
/*!**********************************************!*\
  !*** ./node_modules/axios/lib/core/Axios.js ***!
  \**********************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");
var buildURL = __webpack_require__(/*! ../helpers/buildURL */ "./node_modules/axios/lib/helpers/buildURL.js");
var InterceptorManager = __webpack_require__(/*! ./InterceptorManager */ "./node_modules/axios/lib/core/InterceptorManager.js");
var dispatchRequest = __webpack_require__(/*! ./dispatchRequest */ "./node_modules/axios/lib/core/dispatchRequest.js");
var mergeConfig = __webpack_require__(/*! ./mergeConfig */ "./node_modules/axios/lib/core/mergeConfig.js");
var validator = __webpack_require__(/*! ../helpers/validator */ "./node_modules/axios/lib/helpers/validator.js");

var validators = validator.validators;
/**
 * Create a new instance of Axios
 *
 * @param {Object} instanceConfig The default config for the instance
 */
function Axios(instanceConfig) {
  this.defaults = instanceConfig;
  this.interceptors = {
    request: new InterceptorManager(),
    response: new InterceptorManager()
  };
}

/**
 * Dispatch a request
 *
 * @param {Object} config The config specific for this request (merged with this.defaults)
 */
Axios.prototype.request = function request(configOrUrl, config) {
  /*eslint no-param-reassign:0*/
  // Allow for axios('example/url'[, config]) a la fetch API
  if (typeof configOrUrl === 'string') {
    config = config || {};
    config.url = configOrUrl;
  } else {
    config = configOrUrl || {};
  }

  config = mergeConfig(this.defaults, config);

  // Set config.method
  if (config.method) {
    config.method = config.method.toLowerCase();
  } else if (this.defaults.method) {
    config.method = this.defaults.method.toLowerCase();
  } else {
    config.method = 'get';
  }

  var transitional = config.transitional;

  if (transitional !== undefined) {
    validator.assertOptions(transitional, {
      silentJSONParsing: validators.transitional(validators.boolean),
      forcedJSONParsing: validators.transitional(validators.boolean),
      clarifyTimeoutError: validators.transitional(validators.boolean)
    }, false);
  }

  // filter out skipped interceptors
  var requestInterceptorChain = [];
  var synchronousRequestInterceptors = true;
  this.interceptors.request.forEach(function unshiftRequestInterceptors(interceptor) {
    if (typeof interceptor.runWhen === 'function' && interceptor.runWhen(config) === false) {
      return;
    }

    synchronousRequestInterceptors = synchronousRequestInterceptors && interceptor.synchronous;

    requestInterceptorChain.unshift(interceptor.fulfilled, interceptor.rejected);
  });

  var responseInterceptorChain = [];
  this.interceptors.response.forEach(function pushResponseInterceptors(interceptor) {
    responseInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
  });

  var promise;

  if (!synchronousRequestInterceptors) {
    var chain = [dispatchRequest, undefined];

    Array.prototype.unshift.apply(chain, requestInterceptorChain);
    chain = chain.concat(responseInterceptorChain);

    promise = Promise.resolve(config);
    while (chain.length) {
      promise = promise.then(chain.shift(), chain.shift());
    }

    return promise;
  }


  var newConfig = config;
  while (requestInterceptorChain.length) {
    var onFulfilled = requestInterceptorChain.shift();
    var onRejected = requestInterceptorChain.shift();
    try {
      newConfig = onFulfilled(newConfig);
    } catch (error) {
      onRejected(error);
      break;
    }
  }

  try {
    promise = dispatchRequest(newConfig);
  } catch (error) {
    return Promise.reject(error);
  }

  while (responseInterceptorChain.length) {
    promise = promise.then(responseInterceptorChain.shift(), responseInterceptorChain.shift());
  }

  return promise;
};

Axios.prototype.getUri = function getUri(config) {
  config = mergeConfig(this.defaults, config);
  return buildURL(config.url, config.params, config.paramsSerializer).replace(/^\?/, '');
};

// Provide aliases for supported request methods
utils.forEach(['delete', 'get', 'head', 'options'], function forEachMethodNoData(method) {
  /*eslint func-names:0*/
  Axios.prototype[method] = function(url, config) {
    return this.request(mergeConfig(config || {}, {
      method: method,
      url: url,
      data: (config || {}).data
    }));
  };
});

utils.forEach(['post', 'put', 'patch'], function forEachMethodWithData(method) {
  /*eslint func-names:0*/
  Axios.prototype[method] = function(url, data, config) {
    return this.request(mergeConfig(config || {}, {
      method: method,
      url: url,
      data: data
    }));
  };
});

module.exports = Axios;


/***/ }),

/***/ "./node_modules/axios/lib/core/InterceptorManager.js":
/*!***********************************************************!*\
  !*** ./node_modules/axios/lib/core/InterceptorManager.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

function InterceptorManager() {
  this.handlers = [];
}

/**
 * Add a new interceptor to the stack
 *
 * @param {Function} fulfilled The function to handle `then` for a `Promise`
 * @param {Function} rejected The function to handle `reject` for a `Promise`
 *
 * @return {Number} An ID used to remove interceptor later
 */
InterceptorManager.prototype.use = function use(fulfilled, rejected, options) {
  this.handlers.push({
    fulfilled: fulfilled,
    rejected: rejected,
    synchronous: options ? options.synchronous : false,
    runWhen: options ? options.runWhen : null
  });
  return this.handlers.length - 1;
};

/**
 * Remove an interceptor from the stack
 *
 * @param {Number} id The ID that was returned by `use`
 */
InterceptorManager.prototype.eject = function eject(id) {
  if (this.handlers[id]) {
    this.handlers[id] = null;
  }
};

/**
 * Iterate over all the registered interceptors
 *
 * This method is particularly useful for skipping over any
 * interceptors that may have become `null` calling `eject`.
 *
 * @param {Function} fn The function to call for each interceptor
 */
InterceptorManager.prototype.forEach = function forEach(fn) {
  utils.forEach(this.handlers, function forEachHandler(h) {
    if (h !== null) {
      fn(h);
    }
  });
};

module.exports = InterceptorManager;


/***/ }),

/***/ "./node_modules/axios/lib/core/buildFullPath.js":
/*!******************************************************!*\
  !*** ./node_modules/axios/lib/core/buildFullPath.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var isAbsoluteURL = __webpack_require__(/*! ../helpers/isAbsoluteURL */ "./node_modules/axios/lib/helpers/isAbsoluteURL.js");
var combineURLs = __webpack_require__(/*! ../helpers/combineURLs */ "./node_modules/axios/lib/helpers/combineURLs.js");

/**
 * Creates a new URL by combining the baseURL with the requestedURL,
 * only when the requestedURL is not already an absolute URL.
 * If the requestURL is absolute, this function returns the requestedURL untouched.
 *
 * @param {string} baseURL The base URL
 * @param {string} requestedURL Absolute or relative URL to combine
 * @returns {string} The combined full path
 */
module.exports = function buildFullPath(baseURL, requestedURL) {
  if (baseURL && !isAbsoluteURL(requestedURL)) {
    return combineURLs(baseURL, requestedURL);
  }
  return requestedURL;
};


/***/ }),

/***/ "./node_modules/axios/lib/core/createError.js":
/*!****************************************************!*\
  !*** ./node_modules/axios/lib/core/createError.js ***!
  \****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var enhanceError = __webpack_require__(/*! ./enhanceError */ "./node_modules/axios/lib/core/enhanceError.js");

/**
 * Create an Error with the specified message, config, error code, request and response.
 *
 * @param {string} message The error message.
 * @param {Object} config The config.
 * @param {string} [code] The error code (for example, 'ECONNABORTED').
 * @param {Object} [request] The request.
 * @param {Object} [response] The response.
 * @returns {Error} The created error.
 */
module.exports = function createError(message, config, code, request, response) {
  var error = new Error(message);
  return enhanceError(error, config, code, request, response);
};


/***/ }),

/***/ "./node_modules/axios/lib/core/dispatchRequest.js":
/*!********************************************************!*\
  !*** ./node_modules/axios/lib/core/dispatchRequest.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");
var transformData = __webpack_require__(/*! ./transformData */ "./node_modules/axios/lib/core/transformData.js");
var isCancel = __webpack_require__(/*! ../cancel/isCancel */ "./node_modules/axios/lib/cancel/isCancel.js");
var defaults = __webpack_require__(/*! ../defaults */ "./node_modules/axios/lib/defaults/index.js");
var Cancel = __webpack_require__(/*! ../cancel/Cancel */ "./node_modules/axios/lib/cancel/Cancel.js");

/**
 * Throws a `Cancel` if cancellation has been requested.
 */
function throwIfCancellationRequested(config) {
  if (config.cancelToken) {
    config.cancelToken.throwIfRequested();
  }

  if (config.signal && config.signal.aborted) {
    throw new Cancel('canceled');
  }
}

/**
 * Dispatch a request to the server using the configured adapter.
 *
 * @param {object} config The config that is to be used for the request
 * @returns {Promise} The Promise to be fulfilled
 */
module.exports = function dispatchRequest(config) {
  throwIfCancellationRequested(config);

  // Ensure headers exist
  config.headers = config.headers || {};

  // Transform request data
  config.data = transformData.call(
    config,
    config.data,
    config.headers,
    config.transformRequest
  );

  // Flatten headers
  config.headers = utils.merge(
    config.headers.common || {},
    config.headers[config.method] || {},
    config.headers
  );

  utils.forEach(
    ['delete', 'get', 'head', 'post', 'put', 'patch', 'common'],
    function cleanHeaderConfig(method) {
      delete config.headers[method];
    }
  );

  var adapter = config.adapter || defaults.adapter;

  return adapter(config).then(function onAdapterResolution(response) {
    throwIfCancellationRequested(config);

    // Transform response data
    response.data = transformData.call(
      config,
      response.data,
      response.headers,
      config.transformResponse
    );

    return response;
  }, function onAdapterRejection(reason) {
    if (!isCancel(reason)) {
      throwIfCancellationRequested(config);

      // Transform response data
      if (reason && reason.response) {
        reason.response.data = transformData.call(
          config,
          reason.response.data,
          reason.response.headers,
          config.transformResponse
        );
      }
    }

    return Promise.reject(reason);
  });
};


/***/ }),

/***/ "./node_modules/axios/lib/core/enhanceError.js":
/*!*****************************************************!*\
  !*** ./node_modules/axios/lib/core/enhanceError.js ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";


/**
 * Update an Error with the specified config, error code, and response.
 *
 * @param {Error} error The error to update.
 * @param {Object} config The config.
 * @param {string} [code] The error code (for example, 'ECONNABORTED').
 * @param {Object} [request] The request.
 * @param {Object} [response] The response.
 * @returns {Error} The error.
 */
module.exports = function enhanceError(error, config, code, request, response) {
  error.config = config;
  if (code) {
    error.code = code;
  }

  error.request = request;
  error.response = response;
  error.isAxiosError = true;

  error.toJSON = function toJSON() {
    return {
      // Standard
      message: this.message,
      name: this.name,
      // Microsoft
      description: this.description,
      number: this.number,
      // Mozilla
      fileName: this.fileName,
      lineNumber: this.lineNumber,
      columnNumber: this.columnNumber,
      stack: this.stack,
      // Axios
      config: this.config,
      code: this.code,
      status: this.response && this.response.status ? this.response.status : null
    };
  };
  return error;
};


/***/ }),

/***/ "./node_modules/axios/lib/core/mergeConfig.js":
/*!****************************************************!*\
  !*** ./node_modules/axios/lib/core/mergeConfig.js ***!
  \****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ../utils */ "./node_modules/axios/lib/utils.js");

/**
 * Config-specific merge-function which creates a new config-object
 * by merging two configuration objects together.
 *
 * @param {Object} config1
 * @param {Object} config2
 * @returns {Object} New object resulting from merging config2 to config1
 */
module.exports = function mergeConfig(config1, config2) {
  // eslint-disable-next-line no-param-reassign
  config2 = config2 || {};
  var config = {};

  function getMergedValue(target, source) {
    if (utils.isPlainObject(target) && utils.isPlainObject(source)) {
      return utils.merge(target, source);
    } else if (utils.isPlainObject(source)) {
      return utils.merge({}, source);
    } else if (utils.isArray(source)) {
      return source.slice();
    }
    return source;
  }

  // eslint-disable-next-line consistent-return
  function mergeDeepProperties(prop) {
    if (!utils.isUndefined(config2[prop])) {
      return getMergedValue(config1[prop], config2[prop]);
    } else if (!utils.isUndefined(config1[prop])) {
      return getMergedValue(undefined, config1[prop]);
    }
  }

  // eslint-disable-next-line consistent-return
  function valueFromConfig2(prop) {
    if (!utils.isUndefined(config2[prop])) {
      return getMergedValue(undefined, config2[prop]);
    }
  }

  // eslint-disable-next-line consistent-return
  function defaultToConfig2(prop) {
    if (!utils.isUndefined(config2[prop])) {
      return getMergedValue(undefined, config2[prop]);
    } else if (!utils.isUndefined(config1[prop])) {
      return getMergedValue(undefined, config1[prop]);
    }
  }

  // eslint-disable-next-line consistent-return
  function mergeDirectKeys(prop) {
    if (prop in config2) {
      return getMergedValue(config1[prop], config2[prop]);
    } else if (prop in config1) {
      return getMergedValue(undefined, config1[prop]);
    }
  }

  var mergeMap = {
    'url': valueFromConfig2,
    'method': valueFromConfig2,
    'data': valueFromConfig2,
    'baseURL': defaultToConfig2,
    'transformRequest': defaultToConfig2,
    'transformResponse': defaultToConfig2,
    'paramsSerializer': defaultToConfig2,
    'timeout': defaultToConfig2,
    'timeoutMessage': defaultToConfig2,
    'withCredentials': defaultToConfig2,
    'adapter': defaultToConfig2,
    'responseType': defaultToConfig2,
    'xsrfCookieName': defaultToConfig2,
    'xsrfHeaderName': defaultToConfig2,
    'onUploadProgress': defaultToConfig2,
    'onDownloadProgress': defaultToConfig2,
    'decompress': defaultToConfig2,
    'maxContentLength': defaultToConfig2,
    'maxBodyLength': defaultToConfig2,
    'transport': defaultToConfig2,
    'httpAgent': defaultToConfig2,
    'httpsAgent': defaultToConfig2,
    'cancelToken': defaultToConfig2,
    'socketPath': defaultToConfig2,
    'responseEncoding': defaultToConfig2,
    'validateStatus': mergeDirectKeys
  };

  utils.forEach(Object.keys(config1).concat(Object.keys(config2)), function computeConfigValue(prop) {
    var merge = mergeMap[prop] || mergeDeepProperties;
    var configValue = merge(prop);
    (utils.isUndefined(configValue) && merge !== mergeDirectKeys) || (config[prop] = configValue);
  });

  return config;
};


/***/ }),

/***/ "./node_modules/axios/lib/core/settle.js":
/*!***********************************************!*\
  !*** ./node_modules/axios/lib/core/settle.js ***!
  \***********************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var createError = __webpack_require__(/*! ./createError */ "./node_modules/axios/lib/core/createError.js");

/**
 * Resolve or reject a Promise based on response status.
 *
 * @param {Function} resolve A function that resolves the promise.
 * @param {Function} reject A function that rejects the promise.
 * @param {object} response The response.
 */
module.exports = function settle(resolve, reject, response) {
  var validateStatus = response.config.validateStatus;
  if (!response.status || !validateStatus || validateStatus(response.status)) {
    resolve(response);
  } else {
    reject(createError(
      'Request failed with status code ' + response.status,
      response.config,
      null,
      response.request,
      response
    ));
  }
};


/***/ }),

/***/ "./node_modules/axios/lib/core/transformData.js":
/*!******************************************************!*\
  !*** ./node_modules/axios/lib/core/transformData.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");
var defaults = __webpack_require__(/*! ../defaults */ "./node_modules/axios/lib/defaults/index.js");

/**
 * Transform the data for a request or a response
 *
 * @param {Object|String} data The data to be transformed
 * @param {Array} headers The headers for the request or response
 * @param {Array|Function} fns A single function or Array of functions
 * @returns {*} The resulting transformed data
 */
module.exports = function transformData(data, headers, fns) {
  var context = this || defaults;
  /*eslint no-param-reassign:0*/
  utils.forEach(fns, function transform(fn) {
    data = fn.call(context, data, headers);
  });

  return data;
};


/***/ }),

/***/ "./node_modules/axios/lib/defaults/index.js":
/*!**************************************************!*\
  !*** ./node_modules/axios/lib/defaults/index.js ***!
  \**************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ../utils */ "./node_modules/axios/lib/utils.js");
var normalizeHeaderName = __webpack_require__(/*! ../helpers/normalizeHeaderName */ "./node_modules/axios/lib/helpers/normalizeHeaderName.js");
var enhanceError = __webpack_require__(/*! ../core/enhanceError */ "./node_modules/axios/lib/core/enhanceError.js");
var transitionalDefaults = __webpack_require__(/*! ./transitional */ "./node_modules/axios/lib/defaults/transitional.js");

var DEFAULT_CONTENT_TYPE = {
  'Content-Type': 'application/x-www-form-urlencoded'
};

function setContentTypeIfUnset(headers, value) {
  if (!utils.isUndefined(headers) && utils.isUndefined(headers['Content-Type'])) {
    headers['Content-Type'] = value;
  }
}

function getDefaultAdapter() {
  var adapter;
  if (typeof XMLHttpRequest !== 'undefined') {
    // For browsers use XHR adapter
    adapter = __webpack_require__(/*! ../adapters/xhr */ "./node_modules/axios/lib/adapters/xhr.js");
  } else if (typeof process !== 'undefined' && Object.prototype.toString.call(process) === '[object process]') {
    // For node use HTTP adapter
    adapter = __webpack_require__(/*! ../adapters/http */ "./node_modules/axios/lib/adapters/xhr.js");
  }
  return adapter;
}

function stringifySafely(rawValue, parser, encoder) {
  if (utils.isString(rawValue)) {
    try {
      (parser || JSON.parse)(rawValue);
      return utils.trim(rawValue);
    } catch (e) {
      if (e.name !== 'SyntaxError') {
        throw e;
      }
    }
  }

  return (encoder || JSON.stringify)(rawValue);
}

var defaults = {

  transitional: transitionalDefaults,

  adapter: getDefaultAdapter(),

  transformRequest: [function transformRequest(data, headers) {
    normalizeHeaderName(headers, 'Accept');
    normalizeHeaderName(headers, 'Content-Type');

    if (utils.isFormData(data) ||
      utils.isArrayBuffer(data) ||
      utils.isBuffer(data) ||
      utils.isStream(data) ||
      utils.isFile(data) ||
      utils.isBlob(data)
    ) {
      return data;
    }
    if (utils.isArrayBufferView(data)) {
      return data.buffer;
    }
    if (utils.isURLSearchParams(data)) {
      setContentTypeIfUnset(headers, 'application/x-www-form-urlencoded;charset=utf-8');
      return data.toString();
    }
    if (utils.isObject(data) || (headers && headers['Content-Type'] === 'application/json')) {
      setContentTypeIfUnset(headers, 'application/json');
      return stringifySafely(data);
    }
    return data;
  }],

  transformResponse: [function transformResponse(data) {
    var transitional = this.transitional || defaults.transitional;
    var silentJSONParsing = transitional && transitional.silentJSONParsing;
    var forcedJSONParsing = transitional && transitional.forcedJSONParsing;
    var strictJSONParsing = !silentJSONParsing && this.responseType === 'json';

    if (strictJSONParsing || (forcedJSONParsing && utils.isString(data) && data.length)) {
      try {
        return JSON.parse(data);
      } catch (e) {
        if (strictJSONParsing) {
          if (e.name === 'SyntaxError') {
            throw enhanceError(e, this, 'E_JSON_PARSE');
          }
          throw e;
        }
      }
    }

    return data;
  }],

  /**
   * A timeout in milliseconds to abort a request. If set to 0 (default) a
   * timeout is not created.
   */
  timeout: 0,

  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',

  maxContentLength: -1,
  maxBodyLength: -1,

  validateStatus: function validateStatus(status) {
    return status >= 200 && status < 300;
  },

  headers: {
    common: {
      'Accept': 'application/json, text/plain, */*'
    }
  }
};

utils.forEach(['delete', 'get', 'head'], function forEachMethodNoData(method) {
  defaults.headers[method] = {};
});

utils.forEach(['post', 'put', 'patch'], function forEachMethodWithData(method) {
  defaults.headers[method] = utils.merge(DEFAULT_CONTENT_TYPE);
});

module.exports = defaults;


/***/ }),

/***/ "./node_modules/axios/lib/defaults/transitional.js":
/*!*********************************************************!*\
  !*** ./node_modules/axios/lib/defaults/transitional.js ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";


module.exports = {
  silentJSONParsing: true,
  forcedJSONParsing: true,
  clarifyTimeoutError: false
};


/***/ }),

/***/ "./node_modules/axios/lib/env/data.js":
/*!********************************************!*\
  !*** ./node_modules/axios/lib/env/data.js ***!
  \********************************************/
/***/ ((module) => {

module.exports = {
  "version": "0.26.1"
};

/***/ }),

/***/ "./node_modules/axios/lib/helpers/bind.js":
/*!************************************************!*\
  !*** ./node_modules/axios/lib/helpers/bind.js ***!
  \************************************************/
/***/ ((module) => {

"use strict";


module.exports = function bind(fn, thisArg) {
  return function wrap() {
    var args = new Array(arguments.length);
    for (var i = 0; i < args.length; i++) {
      args[i] = arguments[i];
    }
    return fn.apply(thisArg, args);
  };
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/buildURL.js":
/*!****************************************************!*\
  !*** ./node_modules/axios/lib/helpers/buildURL.js ***!
  \****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

function encode(val) {
  return encodeURIComponent(val).
    replace(/%3A/gi, ':').
    replace(/%24/g, '$').
    replace(/%2C/gi, ',').
    replace(/%20/g, '+').
    replace(/%5B/gi, '[').
    replace(/%5D/gi, ']');
}

/**
 * Build a URL by appending params to the end
 *
 * @param {string} url The base of the url (e.g., http://www.google.com)
 * @param {object} [params] The params to be appended
 * @returns {string} The formatted url
 */
module.exports = function buildURL(url, params, paramsSerializer) {
  /*eslint no-param-reassign:0*/
  if (!params) {
    return url;
  }

  var serializedParams;
  if (paramsSerializer) {
    serializedParams = paramsSerializer(params);
  } else if (utils.isURLSearchParams(params)) {
    serializedParams = params.toString();
  } else {
    var parts = [];

    utils.forEach(params, function serialize(val, key) {
      if (val === null || typeof val === 'undefined') {
        return;
      }

      if (utils.isArray(val)) {
        key = key + '[]';
      } else {
        val = [val];
      }

      utils.forEach(val, function parseValue(v) {
        if (utils.isDate(v)) {
          v = v.toISOString();
        } else if (utils.isObject(v)) {
          v = JSON.stringify(v);
        }
        parts.push(encode(key) + '=' + encode(v));
      });
    });

    serializedParams = parts.join('&');
  }

  if (serializedParams) {
    var hashmarkIndex = url.indexOf('#');
    if (hashmarkIndex !== -1) {
      url = url.slice(0, hashmarkIndex);
    }

    url += (url.indexOf('?') === -1 ? '?' : '&') + serializedParams;
  }

  return url;
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/combineURLs.js":
/*!*******************************************************!*\
  !*** ./node_modules/axios/lib/helpers/combineURLs.js ***!
  \*******************************************************/
/***/ ((module) => {

"use strict";


/**
 * Creates a new URL by combining the specified URLs
 *
 * @param {string} baseURL The base URL
 * @param {string} relativeURL The relative URL
 * @returns {string} The combined URL
 */
module.exports = function combineURLs(baseURL, relativeURL) {
  return relativeURL
    ? baseURL.replace(/\/+$/, '') + '/' + relativeURL.replace(/^\/+/, '')
    : baseURL;
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/cookies.js":
/*!***************************************************!*\
  !*** ./node_modules/axios/lib/helpers/cookies.js ***!
  \***************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

module.exports = (
  utils.isStandardBrowserEnv() ?

  // Standard browser envs support document.cookie
    (function standardBrowserEnv() {
      return {
        write: function write(name, value, expires, path, domain, secure) {
          var cookie = [];
          cookie.push(name + '=' + encodeURIComponent(value));

          if (utils.isNumber(expires)) {
            cookie.push('expires=' + new Date(expires).toGMTString());
          }

          if (utils.isString(path)) {
            cookie.push('path=' + path);
          }

          if (utils.isString(domain)) {
            cookie.push('domain=' + domain);
          }

          if (secure === true) {
            cookie.push('secure');
          }

          document.cookie = cookie.join('; ');
        },

        read: function read(name) {
          var match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
          return (match ? decodeURIComponent(match[3]) : null);
        },

        remove: function remove(name) {
          this.write(name, '', Date.now() - 86400000);
        }
      };
    })() :

  // Non standard browser env (web workers, react-native) lack needed support.
    (function nonStandardBrowserEnv() {
      return {
        write: function write() {},
        read: function read() { return null; },
        remove: function remove() {}
      };
    })()
);


/***/ }),

/***/ "./node_modules/axios/lib/helpers/isAbsoluteURL.js":
/*!*********************************************************!*\
  !*** ./node_modules/axios/lib/helpers/isAbsoluteURL.js ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";


/**
 * Determines whether the specified URL is absolute
 *
 * @param {string} url The URL to test
 * @returns {boolean} True if the specified URL is absolute, otherwise false
 */
module.exports = function isAbsoluteURL(url) {
  // A URL is considered absolute if it begins with "<scheme>://" or "//" (protocol-relative URL).
  // RFC 3986 defines scheme name as a sequence of characters beginning with a letter and followed
  // by any combination of letters, digits, plus, period, or hyphen.
  return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(url);
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/isAxiosError.js":
/*!********************************************************!*\
  !*** ./node_modules/axios/lib/helpers/isAxiosError.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

/**
 * Determines whether the payload is an error thrown by Axios
 *
 * @param {*} payload The value to test
 * @returns {boolean} True if the payload is an error thrown by Axios, otherwise false
 */
module.exports = function isAxiosError(payload) {
  return utils.isObject(payload) && (payload.isAxiosError === true);
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/isURLSameOrigin.js":
/*!***********************************************************!*\
  !*** ./node_modules/axios/lib/helpers/isURLSameOrigin.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

module.exports = (
  utils.isStandardBrowserEnv() ?

  // Standard browser envs have full support of the APIs needed to test
  // whether the request URL is of the same origin as current location.
    (function standardBrowserEnv() {
      var msie = /(msie|trident)/i.test(navigator.userAgent);
      var urlParsingNode = document.createElement('a');
      var originURL;

      /**
    * Parse a URL to discover it's components
    *
    * @param {String} url The URL to be parsed
    * @returns {Object}
    */
      function resolveURL(url) {
        var href = url;

        if (msie) {
        // IE needs attribute set twice to normalize properties
          urlParsingNode.setAttribute('href', href);
          href = urlParsingNode.href;
        }

        urlParsingNode.setAttribute('href', href);

        // urlParsingNode provides the UrlUtils interface - http://url.spec.whatwg.org/#urlutils
        return {
          href: urlParsingNode.href,
          protocol: urlParsingNode.protocol ? urlParsingNode.protocol.replace(/:$/, '') : '',
          host: urlParsingNode.host,
          search: urlParsingNode.search ? urlParsingNode.search.replace(/^\?/, '') : '',
          hash: urlParsingNode.hash ? urlParsingNode.hash.replace(/^#/, '') : '',
          hostname: urlParsingNode.hostname,
          port: urlParsingNode.port,
          pathname: (urlParsingNode.pathname.charAt(0) === '/') ?
            urlParsingNode.pathname :
            '/' + urlParsingNode.pathname
        };
      }

      originURL = resolveURL(window.location.href);

      /**
    * Determine if a URL shares the same origin as the current location
    *
    * @param {String} requestURL The URL to test
    * @returns {boolean} True if URL shares the same origin, otherwise false
    */
      return function isURLSameOrigin(requestURL) {
        var parsed = (utils.isString(requestURL)) ? resolveURL(requestURL) : requestURL;
        return (parsed.protocol === originURL.protocol &&
            parsed.host === originURL.host);
      };
    })() :

  // Non standard browser envs (web workers, react-native) lack needed support.
    (function nonStandardBrowserEnv() {
      return function isURLSameOrigin() {
        return true;
      };
    })()
);


/***/ }),

/***/ "./node_modules/axios/lib/helpers/normalizeHeaderName.js":
/*!***************************************************************!*\
  !*** ./node_modules/axios/lib/helpers/normalizeHeaderName.js ***!
  \***************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ../utils */ "./node_modules/axios/lib/utils.js");

module.exports = function normalizeHeaderName(headers, normalizedName) {
  utils.forEach(headers, function processHeader(value, name) {
    if (name !== normalizedName && name.toUpperCase() === normalizedName.toUpperCase()) {
      headers[normalizedName] = value;
      delete headers[name];
    }
  });
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/parseHeaders.js":
/*!********************************************************!*\
  !*** ./node_modules/axios/lib/helpers/parseHeaders.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var utils = __webpack_require__(/*! ./../utils */ "./node_modules/axios/lib/utils.js");

// Headers whose duplicates are ignored by node
// c.f. https://nodejs.org/api/http.html#http_message_headers
var ignoreDuplicateOf = [
  'age', 'authorization', 'content-length', 'content-type', 'etag',
  'expires', 'from', 'host', 'if-modified-since', 'if-unmodified-since',
  'last-modified', 'location', 'max-forwards', 'proxy-authorization',
  'referer', 'retry-after', 'user-agent'
];

/**
 * Parse headers into an object
 *
 * ```
 * Date: Wed, 27 Aug 2014 08:58:49 GMT
 * Content-Type: application/json
 * Connection: keep-alive
 * Transfer-Encoding: chunked
 * ```
 *
 * @param {String} headers Headers needing to be parsed
 * @returns {Object} Headers parsed into an object
 */
module.exports = function parseHeaders(headers) {
  var parsed = {};
  var key;
  var val;
  var i;

  if (!headers) { return parsed; }

  utils.forEach(headers.split('\n'), function parser(line) {
    i = line.indexOf(':');
    key = utils.trim(line.substr(0, i)).toLowerCase();
    val = utils.trim(line.substr(i + 1));

    if (key) {
      if (parsed[key] && ignoreDuplicateOf.indexOf(key) >= 0) {
        return;
      }
      if (key === 'set-cookie') {
        parsed[key] = (parsed[key] ? parsed[key] : []).concat([val]);
      } else {
        parsed[key] = parsed[key] ? parsed[key] + ', ' + val : val;
      }
    }
  });

  return parsed;
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/spread.js":
/*!**************************************************!*\
  !*** ./node_modules/axios/lib/helpers/spread.js ***!
  \**************************************************/
/***/ ((module) => {

"use strict";


/**
 * Syntactic sugar for invoking a function and expanding an array for arguments.
 *
 * Common use case would be to use `Function.prototype.apply`.
 *
 *  ```js
 *  function f(x, y, z) {}
 *  var args = [1, 2, 3];
 *  f.apply(null, args);
 *  ```
 *
 * With `spread` this example can be re-written.
 *
 *  ```js
 *  spread(function(x, y, z) {})([1, 2, 3]);
 *  ```
 *
 * @param {Function} callback
 * @returns {Function}
 */
module.exports = function spread(callback) {
  return function wrap(arr) {
    return callback.apply(null, arr);
  };
};


/***/ }),

/***/ "./node_modules/axios/lib/helpers/validator.js":
/*!*****************************************************!*\
  !*** ./node_modules/axios/lib/helpers/validator.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var VERSION = (__webpack_require__(/*! ../env/data */ "./node_modules/axios/lib/env/data.js").version);

var validators = {};

// eslint-disable-next-line func-names
['object', 'boolean', 'number', 'function', 'string', 'symbol'].forEach(function(type, i) {
  validators[type] = function validator(thing) {
    return typeof thing === type || 'a' + (i < 1 ? 'n ' : ' ') + type;
  };
});

var deprecatedWarnings = {};

/**
 * Transitional option validator
 * @param {function|boolean?} validator - set to false if the transitional option has been removed
 * @param {string?} version - deprecated version / removed since version
 * @param {string?} message - some message with additional info
 * @returns {function}
 */
validators.transitional = function transitional(validator, version, message) {
  function formatMessage(opt, desc) {
    return '[Axios v' + VERSION + '] Transitional option \'' + opt + '\'' + desc + (message ? '. ' + message : '');
  }

  // eslint-disable-next-line func-names
  return function(value, opt, opts) {
    if (validator === false) {
      throw new Error(formatMessage(opt, ' has been removed' + (version ? ' in ' + version : '')));
    }

    if (version && !deprecatedWarnings[opt]) {
      deprecatedWarnings[opt] = true;
      // eslint-disable-next-line no-console
      console.warn(
        formatMessage(
          opt,
          ' has been deprecated since v' + version + ' and will be removed in the near future'
        )
      );
    }

    return validator ? validator(value, opt, opts) : true;
  };
};

/**
 * Assert object's properties type
 * @param {object} options
 * @param {object} schema
 * @param {boolean?} allowUnknown
 */

function assertOptions(options, schema, allowUnknown) {
  if (typeof options !== 'object') {
    throw new TypeError('options must be an object');
  }
  var keys = Object.keys(options);
  var i = keys.length;
  while (i-- > 0) {
    var opt = keys[i];
    var validator = schema[opt];
    if (validator) {
      var value = options[opt];
      var result = value === undefined || validator(value, opt, options);
      if (result !== true) {
        throw new TypeError('option ' + opt + ' must be ' + result);
      }
      continue;
    }
    if (allowUnknown !== true) {
      throw Error('Unknown option ' + opt);
    }
  }
}

module.exports = {
  assertOptions: assertOptions,
  validators: validators
};


/***/ }),

/***/ "./node_modules/axios/lib/utils.js":
/*!*****************************************!*\
  !*** ./node_modules/axios/lib/utils.js ***!
  \*****************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var bind = __webpack_require__(/*! ./helpers/bind */ "./node_modules/axios/lib/helpers/bind.js");

// utils is a library of generic helper functions non-specific to axios

var toString = Object.prototype.toString;

/**
 * Determine if a value is an Array
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an Array, otherwise false
 */
function isArray(val) {
  return Array.isArray(val);
}

/**
 * Determine if a value is undefined
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if the value is undefined, otherwise false
 */
function isUndefined(val) {
  return typeof val === 'undefined';
}

/**
 * Determine if a value is a Buffer
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Buffer, otherwise false
 */
function isBuffer(val) {
  return val !== null && !isUndefined(val) && val.constructor !== null && !isUndefined(val.constructor)
    && typeof val.constructor.isBuffer === 'function' && val.constructor.isBuffer(val);
}

/**
 * Determine if a value is an ArrayBuffer
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an ArrayBuffer, otherwise false
 */
function isArrayBuffer(val) {
  return toString.call(val) === '[object ArrayBuffer]';
}

/**
 * Determine if a value is a FormData
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an FormData, otherwise false
 */
function isFormData(val) {
  return toString.call(val) === '[object FormData]';
}

/**
 * Determine if a value is a view on an ArrayBuffer
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a view on an ArrayBuffer, otherwise false
 */
function isArrayBufferView(val) {
  var result;
  if ((typeof ArrayBuffer !== 'undefined') && (ArrayBuffer.isView)) {
    result = ArrayBuffer.isView(val);
  } else {
    result = (val) && (val.buffer) && (isArrayBuffer(val.buffer));
  }
  return result;
}

/**
 * Determine if a value is a String
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a String, otherwise false
 */
function isString(val) {
  return typeof val === 'string';
}

/**
 * Determine if a value is a Number
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Number, otherwise false
 */
function isNumber(val) {
  return typeof val === 'number';
}

/**
 * Determine if a value is an Object
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an Object, otherwise false
 */
function isObject(val) {
  return val !== null && typeof val === 'object';
}

/**
 * Determine if a value is a plain Object
 *
 * @param {Object} val The value to test
 * @return {boolean} True if value is a plain Object, otherwise false
 */
function isPlainObject(val) {
  if (toString.call(val) !== '[object Object]') {
    return false;
  }

  var prototype = Object.getPrototypeOf(val);
  return prototype === null || prototype === Object.prototype;
}

/**
 * Determine if a value is a Date
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Date, otherwise false
 */
function isDate(val) {
  return toString.call(val) === '[object Date]';
}

/**
 * Determine if a value is a File
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a File, otherwise false
 */
function isFile(val) {
  return toString.call(val) === '[object File]';
}

/**
 * Determine if a value is a Blob
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Blob, otherwise false
 */
function isBlob(val) {
  return toString.call(val) === '[object Blob]';
}

/**
 * Determine if a value is a Function
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Function, otherwise false
 */
function isFunction(val) {
  return toString.call(val) === '[object Function]';
}

/**
 * Determine if a value is a Stream
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Stream, otherwise false
 */
function isStream(val) {
  return isObject(val) && isFunction(val.pipe);
}

/**
 * Determine if a value is a URLSearchParams object
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a URLSearchParams object, otherwise false
 */
function isURLSearchParams(val) {
  return toString.call(val) === '[object URLSearchParams]';
}

/**
 * Trim excess whitespace off the beginning and end of a string
 *
 * @param {String} str The String to trim
 * @returns {String} The String freed of excess whitespace
 */
function trim(str) {
  return str.trim ? str.trim() : str.replace(/^\s+|\s+$/g, '');
}

/**
 * Determine if we're running in a standard browser environment
 *
 * This allows axios to run in a web worker, and react-native.
 * Both environments support XMLHttpRequest, but not fully standard globals.
 *
 * web workers:
 *  typeof window -> undefined
 *  typeof document -> undefined
 *
 * react-native:
 *  navigator.product -> 'ReactNative'
 * nativescript
 *  navigator.product -> 'NativeScript' or 'NS'
 */
function isStandardBrowserEnv() {
  if (typeof navigator !== 'undefined' && (navigator.product === 'ReactNative' ||
                                           navigator.product === 'NativeScript' ||
                                           navigator.product === 'NS')) {
    return false;
  }
  return (
    typeof window !== 'undefined' &&
    typeof document !== 'undefined'
  );
}

/**
 * Iterate over an Array or an Object invoking a function for each item.
 *
 * If `obj` is an Array callback will be called passing
 * the value, index, and complete array for each item.
 *
 * If 'obj' is an Object callback will be called passing
 * the value, key, and complete object for each property.
 *
 * @param {Object|Array} obj The object to iterate
 * @param {Function} fn The callback to invoke for each item
 */
function forEach(obj, fn) {
  // Don't bother if no value provided
  if (obj === null || typeof obj === 'undefined') {
    return;
  }

  // Force an array if not already something iterable
  if (typeof obj !== 'object') {
    /*eslint no-param-reassign:0*/
    obj = [obj];
  }

  if (isArray(obj)) {
    // Iterate over array values
    for (var i = 0, l = obj.length; i < l; i++) {
      fn.call(null, obj[i], i, obj);
    }
  } else {
    // Iterate over object keys
    for (var key in obj) {
      if (Object.prototype.hasOwnProperty.call(obj, key)) {
        fn.call(null, obj[key], key, obj);
      }
    }
  }
}

/**
 * Accepts varargs expecting each argument to be an object, then
 * immutably merges the properties of each object and returns result.
 *
 * When multiple objects contain the same key the later object in
 * the arguments list will take precedence.
 *
 * Example:
 *
 * ```js
 * var result = merge({foo: 123}, {foo: 456});
 * console.log(result.foo); // outputs 456
 * ```
 *
 * @param {Object} obj1 Object to merge
 * @returns {Object} Result of all merge properties
 */
function merge(/* obj1, obj2, obj3, ... */) {
  var result = {};
  function assignValue(val, key) {
    if (isPlainObject(result[key]) && isPlainObject(val)) {
      result[key] = merge(result[key], val);
    } else if (isPlainObject(val)) {
      result[key] = merge({}, val);
    } else if (isArray(val)) {
      result[key] = val.slice();
    } else {
      result[key] = val;
    }
  }

  for (var i = 0, l = arguments.length; i < l; i++) {
    forEach(arguments[i], assignValue);
  }
  return result;
}

/**
 * Extends object a by mutably adding to it the properties of object b.
 *
 * @param {Object} a The object to be extended
 * @param {Object} b The object to copy properties from
 * @param {Object} thisArg The object to bind function to
 * @return {Object} The resulting value of object a
 */
function extend(a, b, thisArg) {
  forEach(b, function assignValue(val, key) {
    if (thisArg && typeof val === 'function') {
      a[key] = bind(val, thisArg);
    } else {
      a[key] = val;
    }
  });
  return a;
}

/**
 * Remove byte order marker. This catches EF BB BF (the UTF-8 BOM)
 *
 * @param {string} content with BOM
 * @return {string} content value without BOM
 */
function stripBOM(content) {
  if (content.charCodeAt(0) === 0xFEFF) {
    content = content.slice(1);
  }
  return content;
}

module.exports = {
  isArray: isArray,
  isArrayBuffer: isArrayBuffer,
  isBuffer: isBuffer,
  isFormData: isFormData,
  isArrayBufferView: isArrayBufferView,
  isString: isString,
  isNumber: isNumber,
  isObject: isObject,
  isPlainObject: isPlainObject,
  isUndefined: isUndefined,
  isDate: isDate,
  isFile: isFile,
  isBlob: isBlob,
  isFunction: isFunction,
  isStream: isStream,
  isURLSearchParams: isURLSearchParams,
  isStandardBrowserEnv: isStandardBrowserEnv,
  forEach: forEach,
  merge: merge,
  extend: extend,
  trim: trim,
  stripBOM: stripBOM
};


/***/ }),

/***/ "./src/components/Checkbox.js":
/*!************************************!*\
  !*** ./src/components/Checkbox.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


const Checkbox = ({
  label,
  isChecked,
  onChange
}) => {
  const [checked, setChecked] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(isChecked);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    setChecked(isChecked);
  }, [isChecked]);
  const handleCheckboxChange = e => {
    const isChecked = e.target.checked;
    setChecked(isChecked);
    onChange(isChecked); // Pass the state back to the parent component
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "block"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "checkbox",
    checked: checked,
    onChange: handleCheckboxChange,
    className: "form-checkbox h-5 w-5 text-indigo-600 focus:outline-none focus:ring focus:border-indigo-300 rounded"
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    className: "ml-2 text-sm font-medium text-gray-900 dark:text-gray-300"
  }, label));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Checkbox);

/***/ }),

/***/ "./src/components/Filter.js":
/*!**********************************!*\
  !*** ./src/components/Filter.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_responsive__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-responsive */ "./node_modules/react-responsive/dist/react-responsive.js");
/* harmony import */ var react_responsive__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_responsive__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Checkbox__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Checkbox */ "./src/components/Checkbox.js");





const Filter = data => {
  const [originalDisplayedPosts, setOriginalDisplayedPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(data.posts.filter(post => post.product_type !== "musterbestellung").slice(0, 6));
  const [filteredPosts, setFilteredPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [displayedPosts, setDisplayedPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(originalDisplayedPosts);
  const [filters, setFilters] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
    searchText: "",
    purchasability: false,
    metals_and_accessories: 'none',
    color: 'none',
    product_cat: 'none',
    onlineAvailable: false
  });
  const [page, setPage] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1);
  const [loading, setLoading] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [maxPages, setMaxPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(data.maxNumPages);
  const [showFilterItems, setShowFilterItems] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const isMobile = (0,react_responsive__WEBPACK_IMPORTED_MODULE_3__.useMediaQuery)({
    query: `(max-width: 640px)`
  });
  const searchPosts = async pageNum => {
    if (pageNum > maxPages) return;

    // Trigger loading only for the first call to indicate initial data fetching
    if (pageNum === 1) setLoading(true);
    try {
      const response = await axios__WEBPACK_IMPORTED_MODULE_1___default().get(`${data.restUrl}wps/v1/data?post_type=${data.postType}&page=${pageNum}`);
      if (response.data && response.data.posts.length > 0) {
        setOriginalDisplayedPosts(prevPosts => [...prevPosts, ...response.data.posts]);
        if (pageNum === 1) {
          setDisplayedPosts(response.data.posts.slice(0, 6)); // Ensure only the first 6 are set initially
        }
      }
      setMaxPages(response.data.maxPages || maxPages);
    } catch (error) {
      console.error(error);
    } finally {
      // Only stop loading after the last page has been fetched
      if (pageNum === maxPages) {
        setLoading(false);
      }
    }
  };
  const toggleFilterOpen = e => {
    e.preventDefault();
    setShowFilterItems(!showFilterItems);
    if (!showFilterItems) {}
  };
  const loadMorePosts = () => {
    const currentLength = displayedPosts.length;
    const morePosts = filteredPosts.slice(currentLength, currentLength + 6);
    setDisplayedPosts(displayedPosts.concat(morePosts));
  };
  const calculateDisplayedRange = () => {
    const totalPosts = filteredPosts.length;
    const lastPostIndex = displayedPosts.length;
    return `${lastPostIndex} of ${totalPosts} products`;
  };
  const handleTaxSelect = (name, e) => {
    if (name === "purchasability" || name === "metals-and-accessories") {
      setFilters(prevFilters => ({
        ...prevFilters,
        [name]: !prevFilters[name]
      }));
    } else {
      const selectedValue = e.target.value;
      setFilters(prevFilters => ({
        ...prevFilters,
        [name]: selectedValue
      }));
    }
  };
  const applyFilters = ({
    searchText,
    purchasability,
    metals_and_accessories,
    color,
    product_cat,
    onlineAvailable
  }) => {
    let filtered = originalDisplayedPosts;
    if (searchText) {
      filtered = filtered.filter(post => post.post_title.toLowerCase().includes(searchText) || post.excerpt.toLowerCase().includes(searchText) || post.description_text && post.description_text.toLowerCase().includes(searchText) || post.description_title && post.description_title.toLowerCase().includes(searchText) || post.subheadline && post.subheadline.toLowerCase().includes(searchText) || post.features_text && post.features_text.toLowerCase().includes(searchText) || post.areas_of_application && post.areas_of_application.toLowerCase().includes(searchText) || Object.values(post.taxonomies).some(taxonomy => taxonomy.some(term => term.name.toLowerCase().includes(searchText))));
    }
    if (purchasability) {
      filtered = filtered.filter(post => post.taxonomies["purchasability"]?.some(term => term.slug === "muster-verfuegbar"));
    }
    if (metals_and_accessories && metals_and_accessories !== "none") {
      filtered = filtered.filter(post => post.taxonomies["metals-and-accessories"]?.some(term => term.term_id === parseInt(metals_and_accessories)));
    }
    if (color && color !== "none") {
      filtered = filtered.filter(post => post.taxonomies["color"]?.some(term => term.term_id === parseInt(color)));
    }
    if (product_cat && product_cat !== "none") {
      filtered = filtered.filter(post => post.taxonomies["product_cat"]?.some(term => term.term_id === parseInt(product_cat)));
    }
    if (onlineAvailable) {
      filtered = filtered.filter(post => post.price != null && post.price > 0);
    }
    setFilteredPosts(filtered);
    setDisplayedPosts(filtered.slice(0, 6));
  };
  const resetFilters = () => {
    setFilters({
      searchText: "",
      purchasability: false,
      metals_and_accessories: 'none',
      color: 'none',
      product_cat: 'none',
      onlineAvailable: false
    });
  };
  const eventSlider = () => {
    const event = new Event('filterRenderingDone');
    document.dispatchEvent(event);
  };
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const params = new URLSearchParams(window.location.search);

    // Helper function to find the taxonomy ID by slug
    const findTaxonomyIdBySlug = (taxSlug, filterItemOptions) => {
      // Assuming filterItemOptions is an array of { term_id, slug }
      const foundItem = filterItemOptions.find(item => item.slug.toLowerCase() === taxSlug.toLowerCase());
      return foundItem ? foundItem.term_id : 'none';
    };
    const initialFilters = {
      searchText: params.get('text') || "",
      purchasability: params.get('purchasability') === 'true',
      onlineAvailable: params.get('onlineavailable') === 'true'
    };

    // Loop through each filter option to handle dropdowns specifically
    data.filterOptions.forEach(filterItem => {
      if (filterItem.type === "dropdown") {
        const paramName = params.get(filterItem.name);
        if (paramName) {
          initialFilters[filterItem.name] = findTaxonomyIdBySlug(paramName, filterItem.tax_options);
        } else {
          initialFilters[filterItem.name] = 'none';
        }
      }
    });
    setFilters(initialFilters);
    applyFilters(initialFilters);
  }, []);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    applyFilters(filters);
  }, [filters, originalDisplayedPosts]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (data.posts.length && maxPages > 1) {
      setLoading(true); // Start loading
      const fetchAllPages = async () => {
        for (let i = 2; i <= maxPages; i++) {
          await searchPosts(i);
        }
      };
      fetchAllPages().then(() => setLoading(false)); // End loading after all pages are fetched
    }
  }, [maxPages]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!isMobile) setShowFilterItems(true);
  }, [isMobile]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    eventSlider();
  }, [originalDisplayedPosts, displayedPosts]);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", {
    className: " mb-0 sm:mb-[30px]"
  }, data.title)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mx-auto"
  }, isMobile ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full flex justify-end items-center mt-3 sm:mt-[-55px]"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    className: `filter-toggle inline-flex items-center p-2 justify-center text-sm ml-4 ${showFilterItems ? 'open' : 'closed'}`,
    onClick: toggleFilterOpen
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "sr-only"
  }, translation.open_filter), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    className: "w-[2.5rem] h-[2.5rem] open-toggle",
    xmlns: "http://www.w3.org/2000/svg",
    width: "40",
    height: "41",
    viewBox: "0 0 40 41",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    xmlns: "http://www.w3.org/2000/svg",
    id: "Vector",
    d: "M6.25 13.4375H11.6562C11.8714 14.4971 12.4463 15.4498 13.2835 16.1341C14.1207 16.8184 15.1687 17.1922 16.25 17.1922C17.3313 17.1922 18.3793 16.8184 19.2165 16.1341C20.0537 15.4498 20.6286 14.4971 20.8437 13.4375H33.75C33.9986 13.4375 34.2371 13.3387 34.4129 13.1629C34.5887 12.9871 34.6875 12.7486 34.6875 12.5C34.6875 12.2514 34.5887 12.0129 34.4129 11.8371C34.2371 11.6613 33.9986 11.5625 33.75 11.5625H20.8437C20.6286 10.5029 20.0537 9.55018 19.2165 8.8659C18.3793 8.18161 17.3313 7.8078 16.25 7.8078C15.1687 7.8078 14.1207 8.18161 13.2835 8.8659C12.4463 9.55018 11.8714 10.5029 11.6562 11.5625H6.25C6.00136 11.5625 5.7629 11.6613 5.58709 11.8371C5.41127 12.0129 5.3125 12.2514 5.3125 12.5C5.3125 12.7486 5.41127 12.9871 5.58709 13.1629C5.7629 13.3387 6.00136 13.4375 6.25 13.4375ZM16.25 9.6875C16.8063 9.6875 17.35 9.85245 17.8125 10.1615C18.2751 10.4705 18.6355 10.9098 18.8484 11.4237C19.0613 11.9376 19.117 12.5031 19.0085 13.0487C18.8999 13.5943 18.6321 14.0954 18.2387 14.4887C17.8454 14.8821 17.3443 15.1499 16.7987 15.2585C16.2531 15.367 15.6876 15.3113 15.1737 15.0984C14.6598 14.8855 14.2205 14.5251 13.9115 14.0625C13.6025 13.6 13.4375 13.0563 13.4375 12.5C13.4375 11.7541 13.7338 11.0387 14.2613 10.5113C14.7887 9.98382 15.5041 9.6875 16.25 9.6875ZM33.75 26.5625H30.8438C30.6286 25.5029 30.0537 24.5502 29.2165 23.8659C28.3793 23.1816 27.3313 22.8078 26.25 22.8078C25.1687 22.8078 24.1207 23.1816 23.2835 23.8659C22.4463 24.5502 21.8714 25.5029 21.6562 26.5625H6.25C6.00136 26.5625 5.7629 26.6613 5.58709 26.8371C5.41127 27.0129 5.3125 27.2514 5.3125 27.5C5.3125 27.7486 5.41127 27.9871 5.58709 28.1629C5.7629 28.3387 6.00136 28.4375 6.25 28.4375H21.6562C21.8714 29.4971 22.4463 30.4498 23.2835 31.1341C24.1207 31.8184 25.1687 32.1922 26.25 32.1922C27.3313 32.1922 28.3793 31.8184 29.2165 31.1341C30.0537 30.4498 30.6286 29.4971 30.8438 28.4375H33.75C33.9986 28.4375 34.2371 28.3387 34.4129 28.1629C34.5887 27.9871 34.6875 27.7486 34.6875 27.5C34.6875 27.2514 34.5887 27.0129 34.4129 26.8371C34.2371 26.6613 33.9986 26.5625 33.75 26.5625ZM26.25 30.3125C25.6937 30.3125 25.15 30.1475 24.6875 29.8385C24.2249 29.5295 23.8645 29.0902 23.6516 28.5763C23.4387 28.0624 23.383 27.4969 23.4915 26.9513C23.6001 26.4057 23.8679 25.9046 24.2613 25.5113C24.6546 25.1179 25.1557 24.8501 25.7013 24.7415C26.2469 24.633 26.8124 24.6887 27.3263 24.9016C27.8402 25.1145 28.2795 25.4749 28.5885 25.9375C28.8975 26.4 29.0625 26.9437 29.0625 27.5C29.0625 28.2459 28.7662 28.9613 28.2387 29.4887C27.7113 30.0162 26.9959 30.3125 26.25 30.3125Z",
    fill: "black"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    className: "w-[2.5rem] h-[2.5rem] close-toggle",
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 35 35",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M26.25 8.75L8.75 26.25",
    stroke: "#000",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M8.75 8.75L26.25 26.25",
    stroke: "#000",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })))) : null, showFilterItems ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: "filter-items",
    className: "grid grid-cols-12 justify-start mt-[30px] sm:mt-0"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex items-center border-b py-2 col-span-12 max-w-96 mb-7 focus-visible:border-0"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "16",
    height: "16",
    viewBox: "0 0 16 16",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M0.852114 14.3519L4.37266 10.8321C3.35227 9.60705 2.84344 8.03577 2.95204 6.44512C3.06064 4.85447 3.7783 3.36692 4.95573 2.29193C6.13316 1.21693 7.67971 0.637251 9.27365 0.673476C10.8676 0.709701 12.3862 1.35904 13.5136 2.48642C14.641 3.6138 15.2903 5.13241 15.3265 6.72635C15.3627 8.32029 14.7831 9.86684 13.7081 11.0443C12.6331 12.2217 11.1455 12.9394 9.55488 13.048C7.96423 13.1566 6.39295 12.6477 5.1679 11.6273L1.64805 15.1479C1.59579 15.2001 1.53375 15.2416 1.46546 15.2699C1.39718 15.2982 1.32399 15.3127 1.25008 15.3127C1.17617 15.3127 1.10299 15.2982 1.0347 15.2699C0.96642 15.2416 0.904376 15.2001 0.852114 15.1479C0.799852 15.0956 0.758396 15.0336 0.730112 14.9653C0.701828 14.897 0.68727 14.8238 0.68727 14.7499C0.68727 14.676 0.701828 14.6028 0.730112 14.5345C0.758396 14.4663 0.799852 14.4042 0.852114 14.3519ZM14.1876 6.87492C14.1876 5.87365 13.8907 4.89487 13.3344 4.06234C12.7781 3.22982 11.9875 2.58094 11.0624 2.19778C10.1374 1.81461 9.11947 1.71435 8.13744 1.90969C7.15541 2.10503 6.25336 2.58718 5.54536 3.29519C4.83735 4.00319 4.3552 4.90524 4.15986 5.88727C3.96452 6.8693 4.06477 7.8872 4.44794 8.81225C4.83111 9.7373 5.47999 10.528 6.31251 11.0842C7.14503 11.6405 8.12382 11.9374 9.12508 11.9374C10.4673 11.9359 11.7541 11.4021 12.7032 10.453C13.6522 9.50392 14.1861 8.21712 14.1876 6.87492Z",
    fill: "black"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    className: "appearance-none bg-transparent border-none w-full text-[#737373] mr-3 py-1 px-2 leading-tight focus:ring-transparent focus:shadow-none focus-visible:ring-transparent",
    type: "text",
    placeholder: translation.product_search,
    "aria-label": "product search",
    value: filters.searchText // Bind input value to state
    ,
    onChange: e => setFilters({
      ...filters,
      searchText: e.target.value.trim().toLowerCase()
    })
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex flex-col sm:flex-row col-span-12 gap-[1.25rem]"
  }, data.filterOptions.map((filterItem, key) => {
    if (filterItem.type === "dropdown") {
      const filterName = filterItem.name;
      let stateKey;
      let translationKey;
      switch (filterName) {
        case "metals-and-accessories":
          stateKey = "metals_and_accessories";
          translationKey = translation.metals_accessories;
          break;
        case "color":
          stateKey = "color";
          translationKey = translation.colors;
          break;
        case "product_cat":
          stateKey = "product_cat";
          translationKey = translation.product_category;
          break;
        default:
          // Handle unexpected cases
          stateKey = "";
          translationKey = "";
      }
      if (stateKey) {
        return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          key: key,
          className: "col-span-12 relative w-full max-w-full sm:max-w-64 mb-2.5"
        }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, translationKey), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          value: filters[stateKey] !== "none" ? filters[stateKey] : "none" // This will show the current state or "none" if undefined
          ,
          onChange: e => handleTaxSelect(stateKey, e),
          className: "select-filter block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-[0.95rem] pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-[#737373] text-sm"
        }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          value: "none"
        }, "Select ", translationKey), filterItem.tax_options.map(term => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          key: term.term_id,
          value: term.term_id
        }, term.name))));
      }
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mb-5 col-span-12"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: resetFilters,
    className: "text-black text-xs font-normal leading-tight"
  }, "Delete All Filters")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex flex-row gap-[1.25rem] col-span-12"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Checkbox__WEBPACK_IMPORTED_MODULE_2__["default"], {
    label: "Sample Available",
    isChecked: filters.purchasability,
    onChange: isChecked => setFilters(prevFilters => ({
      ...prevFilters,
      purchasability: isChecked
    }))
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col-span-12 block mb-8"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Checkbox__WEBPACK_IMPORTED_MODULE_2__["default"], {
    label: "Online Available",
    isChecked: filters.onlineAvailable,
    onChange: isChecked => setFilters(prevFilters => ({
      ...prevFilters,
      onlineAvailable: isChecked
    }))
  })))) : null), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mt-[54px]"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "grid grid-cols-3 mb-10 gap-y-14 sm:gap-[42px] filter-grid flex flex-wrap"
  }, displayedPosts.length ? displayedPosts.map((post, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    className: "flex flex-col h-full col-span-3 sm:col-span-1 gap-y-14 sm:gap-y-0 flex-grow",
    dangerouslySetInnerHTML: {
      __html: atob(post.html)
    }
  })) : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full text-center"
  }, translation.no_results))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container flex justify-center items-center my-24 flex-col gap-y-6"
  }, loading ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    class: "loading-spinner"
  }) : !loading && displayedPosts.length < filteredPosts.length && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: loadMorePosts,
    disabled: displayedPosts.length >= filteredPosts.length,
    className: "inline-flex items-center gap-x-2.5"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "50",
    height: "50",
    viewBox: "0 0 50 50",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M25 4.6875C20.9826 4.6875 17.0554 5.87881 13.715 8.11077C10.3746 10.3427 7.77111 13.5151 6.23371 17.2267C4.6963 20.9384 4.29405 25.0225 5.07781 28.9628C5.86157 32.903 7.79615 36.5224 10.6369 39.3631C13.4777 42.2039 17.097 44.1384 21.0372 44.9222C24.9775 45.706 29.0616 45.3037 32.7733 43.7663C36.4849 42.2289 39.6573 39.6254 41.8892 36.285C44.1212 32.9446 45.3125 29.0174 45.3125 25C45.3068 19.6145 43.1649 14.4513 39.3568 10.6432C35.5487 6.83507 30.3855 4.69319 25 4.6875ZM25 42.1875C21.6006 42.1875 18.2776 41.1795 15.4511 39.2909C12.6247 37.4023 10.4217 34.718 9.12083 31.5774C7.81995 28.4368 7.47958 24.9809 8.14276 21.6469C8.80595 18.3128 10.4429 15.2503 12.8466 12.8466C15.2503 10.4429 18.3128 8.80594 21.6469 8.14275C24.9809 7.47957 28.4368 7.81994 31.5774 9.12082C34.718 10.4217 37.4023 12.6247 39.2909 15.4511C41.1795 18.2776 42.1875 21.6006 42.1875 25C42.1823 29.5568 40.3699 33.9255 37.1477 37.1477C33.9255 40.3699 29.5568 42.1823 25 42.1875ZM34.375 25C34.375 25.4144 34.2104 25.8118 33.9174 26.1049C33.6243 26.3979 33.2269 26.5625 32.8125 26.5625H26.5625V32.8125C26.5625 33.2269 26.3979 33.6243 26.1049 33.9174C25.8118 34.2104 25.4144 34.375 25 34.375C24.5856 34.375 24.1882 34.2104 23.8952 33.9174C23.6021 33.6243 23.4375 33.2269 23.4375 32.8125V26.5625H17.1875C16.7731 26.5625 16.3757 26.3979 16.0827 26.1049C15.7896 25.8118 15.625 25.4144 15.625 25C15.625 24.5856 15.7896 24.1882 16.0827 23.8951C16.3757 23.6021 16.7731 23.4375 17.1875 23.4375H23.4375V17.1875C23.4375 16.7731 23.6021 16.3757 23.8952 16.0826C24.1882 15.7896 24.5856 15.625 25 15.625C25.4144 15.625 25.8118 15.7896 26.1049 16.0826C26.3979 16.3757 26.5625 16.7731 26.5625 17.1875V23.4375H32.8125C33.2269 23.4375 33.6243 23.6021 33.9174 23.8951C34.2104 24.1882 34.375 24.5856 34.375 25Z",
    fill: "black"
  })), translation.load_more), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "text-base leading-normal italic"
  }, calculateDisplayedRange())));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Filter);

/***/ }),

/***/ "./src/components/FilterShop.js":
/*!**************************************!*\
  !*** ./src/components/FilterShop.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_responsive__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-responsive */ "./node_modules/react-responsive/dist/react-responsive.js");
/* harmony import */ var react_responsive__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_responsive__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Checkbox__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Checkbox */ "./src/components/Checkbox.js");





const FilterShop = data => {
  const initialPosts = data.posts.filter(post => post.price > 0);
  const [originalDisplayedPosts, setOriginalDisplayedPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initialPosts.slice(0, 6));
  const [filteredPosts, setFilteredPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initialPosts);
  const [displayedPosts, setDisplayedPosts] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initialPosts.slice(0, 6));
  const [filters, setFilters] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
    searchText: "",
    purchasability: false,
    metals_and_accessories: 'none'
  });
  const [page, setPage] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1);
  const [loading, setLoading] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [maxPages, setMaxPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(data.maxNumPages);
  const [showFilterItems, setShowFilterItems] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const isMobile = (0,react_responsive__WEBPACK_IMPORTED_MODULE_3__.useMediaQuery)({
    query: `(max-width: 640px)`
  });
  const searchPosts = async pageNum => {
    if (pageNum > maxPages) return;
    if (pageNum === 1) setLoading(true);
    try {
      const response = await axios__WEBPACK_IMPORTED_MODULE_1___default().get(`${data.restUrl}wps/v1/data?post_type=${data.postType}&page=${pageNum}&min_price=0.01`);
      if (response.data && response.data.posts.length > 0) {
        setOriginalDisplayedPosts(prevPosts => [...prevPosts, ...response.data.posts]);
        if (pageNum === 1) {
          setDisplayedPosts(response.data.posts.slice(0, 6));
        }
      }
      setMaxPages(response.data.maxPages || maxPages);
    } catch (error) {
      console.error(error);
    } finally {
      if (pageNum === maxPages) {
        setLoading(false);
      }
    }
  };
  const toggleFilterOpen = e => {
    e.preventDefault();
    setShowFilterItems(!showFilterItems);
  };
  const loadMorePosts = () => {
    const currentLength = displayedPosts.length;
    const morePosts = filteredPosts.slice(currentLength, currentLength + 6);
    setDisplayedPosts(displayedPosts.concat(morePosts));
  };
  const calculateDisplayedRange = () => {
    const totalPosts = filteredPosts.length;
    const lastPostIndex = displayedPosts.length;
    return `${lastPostIndex} of ${totalPosts} products`;
  };
  const handleTaxSelect = (name, e) => {
    const selectedValue = e.target.value;
    setFilters(prevFilters => ({
      ...prevFilters,
      [name]: selectedValue
    }));
  };
  const applyFilters = ({
    searchText,
    purchasability,
    metals_and_accessories
  }) => {
    let filtered = originalDisplayedPosts;
    if (searchText) {
      filtered = filtered.filter(post => post.post_title.toLowerCase().includes(searchText) || post.excerpt.toLowerCase().includes(searchText) || post.description_text && post.description_text.toLowerCase().includes(searchText) || post.description_title && post.description_title.toLowerCase().includes(searchText) || post.subheadline && post.subheadline.toLowerCase().includes(searchText) || post.features_text && post.features_text.toLowerCase().includes(searchText) || post.areas_of_application && post.areas_of_application.toLowerCase().includes(searchText) || Object.values(post.taxonomies).some(taxonomy => taxonomy.some(term => term.name.toLowerCase().includes(searchText))));
    }
    if (purchasability) {
      filtered = filtered.filter(post => post.taxonomies["purchasability"]?.some(term => term.slug === "muster-verfuegbar"));
    }
    if (metals_and_accessories && metals_and_accessories !== "none") {
      filtered = filtered.filter(post => post.taxonomies["metals-and-accessories"]?.some(term => term.term_id === parseInt(metals_and_accessories)));
    }
    setFilteredPosts(filtered);
    setDisplayedPosts(filtered.slice(0, 6));
  };
  const resetFilters = () => {
    setFilters({
      searchText: "",
      purchasability: false,
      metals_and_accessories: 'none'
    });
  };
  const eventSlider = () => {
    const event = new Event('filterRenderingDone');
    document.dispatchEvent(event);
  };
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const params = new URLSearchParams(window.location.search);
    const findTaxonomyIdBySlug = (taxSlug, filterItemOptions) => {
      const foundItem = filterItemOptions.find(item => item.slug.toLowerCase() === taxSlug.toLowerCase());
      return foundItem ? foundItem.term_id : 'none';
    };
    const initialFilters = {
      searchText: params.get('text') || "",
      purchasability: params.get('purchasability') === 'true'
    };
    data.filterOptions.forEach(filterItem => {
      if (filterItem.name === "metals-and-accessories") {
        const paramName = params.get(filterItem.name);
        if (paramName) {
          initialFilters[filterItem.name] = findTaxonomyIdBySlug(paramName, filterItem.tax_options);
        } else {
          initialFilters[filterItem.name] = 'none';
        }
      }
    });
    setFilters(initialFilters);
    applyFilters(initialFilters);
  }, []);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    applyFilters(filters);
  }, [filters, originalDisplayedPosts]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (maxPages > 1) {
      setLoading(true);
      const fetchAllPages = async () => {
        for (let i = 2; i <= maxPages; i++) {
          await searchPosts(i);
        }
      };
      fetchAllPages().then(() => setLoading(false));
    }
  }, [maxPages]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!isMobile) setShowFilterItems(true);
  }, [isMobile]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    eventSlider();
  }, [originalDisplayedPosts, displayedPosts]);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", {
    className: "mb-0 sm:mb-[30px]"
  }, data.title)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mx-auto"
  }, isMobile ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full flex justify-end items-center mt-3 sm:mt-[-55px]"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    className: `filter-toggle inline-flex items-center p-2 justify-center text-sm ml-4 ${showFilterItems ? 'open' : 'closed'}`,
    onClick: toggleFilterOpen
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "sr-only"
  }, translation.open_filter), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    className: "w-[2.5rem] h-[2.5rem] open-toggle",
    xmlns: "http://www.w3.org/2000/svg",
    width: "40",
    height: "41",
    viewBox: "0 0 40 41",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    xmlns: "http://www.w3.org/2000/svg",
    id: "Vector",
    d: "M6.25 13.4375H11.6562C11.8714 14.4971 12.4463 15.4498 13.2835 16.1341C14.1207 16.8184 15.1687 17.1922 16.25 17.1922C17.3313 17.1922 18.3793 16.8184 19.2165 16.1341C20.0537 15.4498 20.6286 14.4971 20.8437 13.4375H33.75C33.9986 13.4375 34.2371 13.3387 34.4129 13.1629C34.5887 12.9871 34.6875 12.7486 34.6875 12.5C34.6875 12.2514 34.5887 12.0129 34.4129 11.8371C34.2371 11.6613 33.9986 11.5625 33.75 11.5625H20.8437C20.6286 10.5029 20.0537 9.55018 19.2165 8.8659C18.3793 8.18161 17.3313 7.8078 16.25 7.8078C15.1687 7.8078 14.1207 8.18161 13.2835 8.8659C12.4463 9.55018 11.8714 10.5029 11.6562 11.5625H6.25C6.00136 11.5625 5.7629 11.6613 5.58709 11.8371C5.41127 12.0129 5.3125 12.2514 5.3125 12.5C5.3125 12.7486 5.41127 12.9871 5.58709 13.1629C5.7629 13.3387 6.00136 13.4375 6.25 13.4375ZM16.25 9.6875C16.8063 9.6875 17.35 9.85245 17.8125 10.1615C18.2751 10.4705 18.6355 10.9098 18.8484 11.4237C19.0613 11.9376 19.117 12.5031 19.0085 13.0487C18.8999 13.5943 18.6321 14.0954 18.2387 14.4887C17.8454 14.8821 17.3443 15.1499 16.7987 15.2585C16.2531 15.367 15.6876 15.3113 15.1737 15.0984C14.6598 14.8855 14.2205 14.5251 13.9115 14.0625C13.6025 13.6 13.4375 13.0563 13.4375 12.5C13.4375 11.7541 13.7338 11.0387 14.2613 10.5113C14.7887 9.98382 15.5041 9.6875 16.25 9.6875ZM33.75 26.5625H30.8438C30.6286 25.5029 30.0537 24.5502 29.2165 23.8659C28.3793 23.1816 27.3313 22.8078 26.25 22.8078C25.1687 22.8078 24.1207 23.1816 23.2835 23.8659C22.4463 24.5502 21.8714 25.5029 21.6562 26.5625H6.25C6.00136 26.5625 5.7629 26.6613 5.58709 26.8371C5.41127 27.0129 5.3125 27.2514 5.3125 27.5C5.3125 27.7486 5.41127 27.9871 5.58709 28.1629C5.7629 28.3387 6.00136 28.4375 6.25 28.4375H21.6562C21.8714 29.4971 22.4463 30.4498 23.2835 31.1341C24.1207 31.8184 25.1687 32.1922 26.25 32.1922C27.3313 32.1922 28.3793 31.8184 29.2165 31.1341C30.0537 30.4498 30.6286 29.4971 30.8438 28.4375H33.75C33.9986 28.4375 34.2371 28.3387 34.4129 28.1629C34.5887 27.9871 34.6875 27.7486 34.6875 27.5C34.6875 27.2514 34.5887 27.0129 34.4129 26.8371C34.2371 26.6613 33.9986 26.5625 33.75 26.5625ZM26.25 30.3125C25.6937 30.3125 25.15 30.1475 24.6875 29.8385C24.2249 29.5295 23.8645 29.0902 23.6516 28.5763C23.4387 28.0624 23.383 27.4969 23.4915 26.9513C23.6001 26.4057 23.8679 25.9046 24.2613 25.5113C24.6546 25.1179 25.1557 24.8501 25.7013 24.7415C26.2469 24.633 26.8124 24.6887 27.3263 24.9016C27.8402 25.1145 28.2795 25.4749 28.5885 25.9375C28.8975 26.4 29.0625 26.9437 29.0625 27.5C29.0625 28.2459 28.7662 28.9613 28.2387 29.4887C27.7113 30.0162 26.9959 30.3125 26.25 30.3125Z",
    fill: "black"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    className: "w-[2.5rem] h-[2.5rem] close-toggle",
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 35 35",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M26.25 8.75L8.75 26.25",
    stroke: "#000",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M8.75 8.75L26.25 26.25",
    stroke: "#000",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  })))) : null, showFilterItems ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    id: "filter-items",
    className: "grid grid-cols-12 justify-start mt-[30px] sm:mt-0"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex items-center border-b py-2 col-span-12 max-w-96 mb-7 focus-visible:border-0"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "16",
    height: "16",
    viewBox: "0 0 16 16",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M0.852114 14.3519L4.37266 10.8321C3.35227 9.60705 2.84344 8.03577 2.95204 6.44512C3.06064 4.85447 3.7783 3.36692 4.95573 2.29193C6.13316 1.21693 7.67971 0.637251 9.27365 0.673476C10.8676 0.709701 12.3862 1.35904 13.5136 2.48642C14.641 3.6138 15.2903 5.13241 15.3265 6.72635C15.3627 8.32029 14.7831 9.86684 13.7081 11.0443C12.6331 12.2217 11.1455 12.9394 9.55488 13.048C7.96423 13.1566 6.39295 12.6477 5.1679 11.6273L1.64805 15.1479C1.59579 15.2001 1.53375 15.2416 1.46546 15.2699C1.39718 15.2982 1.32399 15.3127 1.25008 15.3127C1.17617 15.3127 1.10299 15.2982 1.0347 15.2699C0.96642 15.2416 0.904376 15.2001 0.852114 15.1479C0.799852 15.0956 0.758396 15.0336 0.730112 14.9653C0.701828 14.897 0.68727 14.8238 0.68727 14.7499C0.68727 14.676 0.701828 14.6028 0.730112 14.5345C0.758396 14.4663 0.799852 14.4042 0.852114 14.3519ZM14.1876 6.87492C14.1876 5.87365 13.8907 4.89487 13.3344 4.06234C12.7781 3.22982 11.9875 2.58094 11.0624 2.19778C10.1374 1.81461 9.11947 1.71435 8.13744 1.90969C7.15541 2.10503 6.25336 2.58718 5.54536 3.29519C4.83735 4.00319 4.3552 4.90524 4.15986 5.88727C3.96452 6.8693 4.06477 7.8872 4.44794 8.81225C4.83111 9.7373 5.47999 10.528 6.31251 11.0842C7.14503 11.6405 8.12382 11.9374 9.12508 11.9374C10.4673 11.9359 11.7541 11.4021 12.7032 10.453C13.6522 9.50392 14.1861 8.21712 14.1876 6.87492Z",
    fill: "black"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    className: "appearance-none bg-transparent border-none w-full text-[#737373] mr-3 py-1 px-2 leading-tight focus:ring-transparent focus:shadow-none focus-visible:ring-transparent",
    type: "text",
    placeholder: translation.product_search,
    "aria-label": "product search",
    value: filters.searchText // Bind input value to state
    ,
    onChange: e => setFilters({
      ...filters,
      searchText: e.target.value.trim().toLowerCase()
    })
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex flex-col sm:flex-row col-span-12 gap-[1.25rem]"
  }, data.filterOptions.map((filterItem, key) => {
    if (filterItem.name === "metals-and-accessories") {
      const filterName = filterItem.name;
      const stateKey = "metals_and_accessories";
      const translationKey = translation.metals_accessories;
      return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        key: key,
        className: "col-span-12 relative w-full max-w-full sm:max-w-64 mb-2.5"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, translationKey), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        value: filters[stateKey] !== "none" ? filters[stateKey] : "none",
        onChange: e => handleTaxSelect(stateKey, e),
        className: "select-filter block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-[0.95rem] pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-[#737373] text-sm"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "none"
      }, "Select ", translationKey), filterItem.tax_options.map(term => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        key: term.term_id,
        value: term.term_id
      }, term.name))));
    }
    return null;
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mb-5 col-span-12"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: resetFilters,
    className: "text-black text-xs font-normal leading-tight"
  }, "Delete All Filters")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "flex flex-row gap-[1.25rem] col-span-12"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Checkbox__WEBPACK_IMPORTED_MODULE_2__["default"], {
    label: "Sample Available",
    isChecked: filters.purchasability,
    onChange: isChecked => setFilters(prevFilters => ({
      ...prevFilters,
      purchasability: isChecked
    }))
  })))) : null), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container mt-[54px]"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "grid grid-cols-3 mb-10 gap-y-14 sm:gap-[42px] filter-grid flex flex-wrap"
  }, displayedPosts.length ? displayedPosts.map((post, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    className: "flex flex-col h-full col-span-3 sm:col-span-1 gap-y-14 sm:gap-y-0 flex-grow",
    dangerouslySetInnerHTML: {
      __html: atob(post.html)
    }
  })) : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "w-full text-center"
  }, translation.no_results))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container flex justify-center items-center my-24 flex-col gap-y-6"
  }, loading ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "loading-spinner"
  }) : !loading && displayedPosts.length < filteredPosts.length && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: loadMorePosts,
    disabled: displayedPosts.length >= filteredPosts.length,
    className: "inline-flex items-center gap-x-2.5"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "50",
    height: "50",
    viewBox: "0 0 50 50",
    fill: "none"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M25 4.6875C20.9826 4.6875 17.0554 5.87881 13.715 8.11077C10.3746 10.3427 7.77111 13.5151 6.23371 17.2267C4.6963 20.9384 4.29405 25.0225 5.07781 28.9628C5.86157 32.903 7.79615 36.5224 10.6369 39.3631C13.4777 42.2039 17.097 44.1384 21.0372 44.9222C24.9775 45.706 29.0616 45.3037 32.7733 43.7663C36.4849 42.2289 39.6573 39.6254 41.8892 36.285C44.1212 32.9446 45.3125 29.0174 45.3125 25C45.3068 19.6145 43.1649 14.4513 39.3568 10.6432C35.5487 6.83507 30.3855 4.69319 25 4.6875ZM25 42.1875C21.6006 42.1875 18.2776 41.1795 15.4511 39.2909C12.6247 37.4023 10.4217 34.718 9.12083 31.5774C7.81995 28.4368 7.47958 24.9809 8.14276 21.6469C8.80595 18.3128 10.4429 15.2503 12.8466 12.8466C15.2503 10.4429 18.3128 8.80594 21.6469 8.14275C24.9809 7.47957 28.4368 7.81994 31.5774 9.12082C34.718 10.4217 37.4023 12.6247 39.2909 15.4511C41.1795 18.2776 42.1875 21.6006 42.1875 25C42.1823 29.5568 40.3699 33.9255 37.1477 37.1477C33.9255 40.3699 29.5568 42.1823 25 42.1875ZM34.375 25C34.375 25.4144 34.2104 25.8118 33.9174 26.1049C33.6243 26.3979 33.2269 26.5625 32.8125 26.5625H26.5625V32.8125C26.5625 33.2269 26.3979 33.6243 26.1049 33.9174C25.8118 34.2104 25.4144 34.375 25 34.375C24.5856 34.375 24.1882 34.2104 23.8952 33.9174C23.6021 33.6243 23.4375 33.2269 23.4375 32.8125V26.5625H17.1875C16.7731 26.5625 16.3757 26.3979 16.0827 26.1049C15.7896 25.8118 15.625 25.4144 15.625 25C15.625 24.5856 15.7896 24.1882 16.0827 23.8951C16.3757 23.6021 16.7731 23.4375 17.1875 23.4375H23.4375V17.1875C23.4375 16.7731 23.6021 16.3757 23.8952 16.0826C24.1882 15.7896 24.5856 15.625 25 15.625C25.4144 15.625 25.8118 15.7896 26.1049 16.0826C26.3979 16.3757 26.5625 16.7731 26.5625 17.1875V23.4375H32.8125C33.2269 23.4375 33.6243 23.6021 33.9174 23.8951C34.2104 24.1882 34.375 24.5856 34.375 25Z",
    fill: "black"
  })), translation.load_more), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "text-base leading-normal italic"
  }, calculateDisplayedRange())));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FilterShop);

/***/ }),

/***/ "./node_modules/react-responsive/dist/react-responsive.js":
/*!****************************************************************!*\
  !*** ./node_modules/react-responsive/dist/react-responsive.js ***!
  \****************************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

(function webpackUniversalModuleDefinition(root, factory) {
	if(true)
		module.exports = factory(__webpack_require__(/*! react */ "react"));
	else {}
})(this, (__WEBPACK_EXTERNAL_MODULE_react__) => {
return /******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/css-mediaquery/index.js":
/*!**********************************************!*\
  !*** ./node_modules/css-mediaquery/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
/*
Copyright (c) 2014, Yahoo! Inc. All rights reserved.
Copyrights licensed under the New BSD License.
See the accompanying LICENSE file for terms.
*/



exports.match = matchQuery;
exports.parse = parseQuery;

// -----------------------------------------------------------------------------

var RE_MEDIA_QUERY     = /(?:(only|not)?\s*([^\s\(\)]+)(?:\s*and)?\s*)?(.+)?/i,
    RE_MQ_EXPRESSION   = /\(\s*([^\s\:\)]+)\s*(?:\:\s*([^\s\)]+))?\s*\)/,
    RE_MQ_FEATURE      = /^(?:(min|max)-)?(.+)/,
    RE_LENGTH_UNIT     = /(em|rem|px|cm|mm|in|pt|pc)?$/,
    RE_RESOLUTION_UNIT = /(dpi|dpcm|dppx)?$/;

function matchQuery(mediaQuery, values) {
    return parseQuery(mediaQuery).some(function (query) {
        var inverse = query.inverse;

        // Either the parsed or specified `type` is "all", or the types must be
        // equal for a match.
        var typeMatch = query.type === 'all' || values.type === query.type;

        // Quit early when `type` doesn't match, but take "not" into account.
        if ((typeMatch && inverse) || !(typeMatch || inverse)) {
            return false;
        }

        var expressionsMatch = query.expressions.every(function (expression) {
            var feature  = expression.feature,
                modifier = expression.modifier,
                expValue = expression.value,
                value    = values[feature];

            // Missing or falsy values don't match.
            if (!value) { return false; }

            switch (feature) {
                case 'orientation':
                case 'scan':
                    return value.toLowerCase() === expValue.toLowerCase();

                case 'width':
                case 'height':
                case 'device-width':
                case 'device-height':
                    expValue = toPx(expValue);
                    value    = toPx(value);
                    break;

                case 'resolution':
                    expValue = toDpi(expValue);
                    value    = toDpi(value);
                    break;

                case 'aspect-ratio':
                case 'device-aspect-ratio':
                case /* Deprecated */ 'device-pixel-ratio':
                    expValue = toDecimal(expValue);
                    value    = toDecimal(value);
                    break;

                case 'grid':
                case 'color':
                case 'color-index':
                case 'monochrome':
                    expValue = parseInt(expValue, 10) || 1;
                    value    = parseInt(value, 10) || 0;
                    break;
            }

            switch (modifier) {
                case 'min': return value >= expValue;
                case 'max': return value <= expValue;
                default   : return value === expValue;
            }
        });

        return (expressionsMatch && !inverse) || (!expressionsMatch && inverse);
    });
}

function parseQuery(mediaQuery) {
    return mediaQuery.split(',').map(function (query) {
        query = query.trim();

        var captures    = query.match(RE_MEDIA_QUERY),
            modifier    = captures[1],
            type        = captures[2],
            expressions = captures[3] || '',
            parsed      = {};

        parsed.inverse = !!modifier && modifier.toLowerCase() === 'not';
        parsed.type    = type ? type.toLowerCase() : 'all';

        // Split expressions into a list.
        expressions = expressions.match(/\([^\)]+\)/g) || [];

        parsed.expressions = expressions.map(function (expression) {
            var captures = expression.match(RE_MQ_EXPRESSION),
                feature  = captures[1].toLowerCase().match(RE_MQ_FEATURE);

            return {
                modifier: feature[1],
                feature : feature[2],
                value   : captures[2]
            };
        });

        return parsed;
    });
}

// -- Utilities ----------------------------------------------------------------

function toDecimal(ratio) {
    var decimal = Number(ratio),
        numbers;

    if (!decimal) {
        numbers = ratio.match(/^(\d+)\s*\/\s*(\d+)$/);
        decimal = numbers[1] / numbers[2];
    }

    return decimal;
}

function toDpi(resolution) {
    var value = parseFloat(resolution),
        units = String(resolution).match(RE_RESOLUTION_UNIT)[1];

    switch (units) {
        case 'dpcm': return value / 2.54;
        case 'dppx': return value * 96;
        default    : return value;
    }
}

function toPx(length) {
    var value = parseFloat(length),
        units = String(length).match(RE_LENGTH_UNIT)[1];

    switch (units) {
        case 'em' : return value * 16;
        case 'rem': return value * 16;
        case 'cm' : return value * 96 / 2.54;
        case 'mm' : return value * 96 / 2.54 / 10;
        case 'in' : return value * 96;
        case 'pt' : return value * 72;
        case 'pc' : return value * 72 / 12;
        default   : return value;
    }
}


/***/ }),

/***/ "./node_modules/hyphenate-style-name/index.js":
/*!****************************************************!*\
  !*** ./node_modules/hyphenate-style-name/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __nested_webpack_exports__, __nested_webpack_require_6053__) => {

"use strict";
__nested_webpack_require_6053__.r(__nested_webpack_exports__);
/* harmony export */ __nested_webpack_require_6053__.d(__nested_webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* eslint-disable no-var, prefer-template */
var uppercasePattern = /[A-Z]/g
var msPattern = /^ms-/
var cache = {}

function toHyphenLower(match) {
  return '-' + match.toLowerCase()
}

function hyphenateStyleName(name) {
  if (cache.hasOwnProperty(name)) {
    return cache[name]
  }

  var hName = name.replace(uppercasePattern, toHyphenLower)
  return (cache[name] = msPattern.test(hName) ? '-' + hName : hName)
}

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (hyphenateStyleName);


/***/ }),

/***/ "./node_modules/matchmediaquery/index.js":
/*!***********************************************!*\
  !*** ./node_modules/matchmediaquery/index.js ***!
  \***********************************************/
/***/ ((module, __unused_webpack_exports, __nested_webpack_require_7064__) => {

"use strict";


var staticMatch = (__nested_webpack_require_7064__(/*! css-mediaquery */ "./node_modules/css-mediaquery/index.js").match);
var dynamicMatch = typeof window !== 'undefined' ? window.matchMedia : null;

// our fake MediaQueryList
function Mql(query, values, forceStatic){
  var self = this;
  if(dynamicMatch && !forceStatic){
    var mql = dynamicMatch.call(window, query);
    this.matches = mql.matches;
    this.media = mql.media;
    // TODO: is there a time it makes sense to remove this listener?
    mql.addListener(update);
  } else {
    this.matches = staticMatch(query, values);
    this.media = query;
  }

  this.addListener = addListener;
  this.removeListener = removeListener;
  this.dispose = dispose;

  function addListener(listener){
    if(mql){
      mql.addListener(listener);
    }
  }

  function removeListener(listener){
    if(mql){
      mql.removeListener(listener);
    }
  }

  // update ourselves!
  function update(evt){
    self.matches = evt.matches;
    self.media = evt.media;
  }

  function dispose(){
    if(mql){
      mql.removeListener(update);
    }
  }
}

function matchMedia(query, values, forceStatic){
  return new Mql(query, values, forceStatic);
}

module.exports = matchMedia;


/***/ }),

/***/ "./node_modules/object-assign/index.js":
/*!*********************************************!*\
  !*** ./node_modules/object-assign/index.js ***!
  \*********************************************/
/***/ ((module) => {

"use strict";
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/


/* eslint-disable no-unused-vars */
var getOwnPropertySymbols = Object.getOwnPropertySymbols;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var propIsEnumerable = Object.prototype.propertyIsEnumerable;

function toObject(val) {
	if (val === null || val === undefined) {
		throw new TypeError('Object.assign cannot be called with null or undefined');
	}

	return Object(val);
}

function shouldUseNative() {
	try {
		if (!Object.assign) {
			return false;
		}

		// Detect buggy property enumeration order in older V8 versions.

		// https://bugs.chromium.org/p/v8/issues/detail?id=4118
		var test1 = new String('abc');  // eslint-disable-line no-new-wrappers
		test1[5] = 'de';
		if (Object.getOwnPropertyNames(test1)[0] === '5') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test2 = {};
		for (var i = 0; i < 10; i++) {
			test2['_' + String.fromCharCode(i)] = i;
		}
		var order2 = Object.getOwnPropertyNames(test2).map(function (n) {
			return test2[n];
		});
		if (order2.join('') !== '0123456789') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test3 = {};
		'abcdefghijklmnopqrst'.split('').forEach(function (letter) {
			test3[letter] = letter;
		});
		if (Object.keys(Object.assign({}, test3)).join('') !==
				'abcdefghijklmnopqrst') {
			return false;
		}

		return true;
	} catch (err) {
		// We don't expect any of the above to throw, but better to be safe.
		return false;
	}
}

module.exports = shouldUseNative() ? Object.assign : function (target, source) {
	var from;
	var to = toObject(target);
	var symbols;

	for (var s = 1; s < arguments.length; s++) {
		from = Object(arguments[s]);

		for (var key in from) {
			if (hasOwnProperty.call(from, key)) {
				to[key] = from[key];
			}
		}

		if (getOwnPropertySymbols) {
			symbols = getOwnPropertySymbols(from);
			for (var i = 0; i < symbols.length; i++) {
				if (propIsEnumerable.call(from, symbols[i])) {
					to[symbols[i]] = from[symbols[i]];
				}
			}
		}
	}

	return to;
};


/***/ }),

/***/ "./node_modules/prop-types/checkPropTypes.js":
/*!***************************************************!*\
  !*** ./node_modules/prop-types/checkPropTypes.js ***!
  \***************************************************/
/***/ ((module, __unused_webpack_exports, __nested_webpack_require_10944__) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var printWarning = function() {};

if (true) {
  var ReactPropTypesSecret = __nested_webpack_require_10944__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
  var loggedTypeFailures = {};
  var has = __nested_webpack_require_10944__(/*! ./lib/has */ "./node_modules/prop-types/lib/has.js");

  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) { /**/ }
  };
}

/**
 * Assert that the values match with the type specs.
 * Error messages are memorized and will only be shown once.
 *
 * @param {object} typeSpecs Map of name to a ReactPropType
 * @param {object} values Runtime values that need to be type-checked
 * @param {string} location e.g. "prop", "context", "child context"
 * @param {string} componentName Name of the component for error messages.
 * @param {?Function} getStack Returns the component stack.
 * @private
 */
function checkPropTypes(typeSpecs, values, location, componentName, getStack) {
  if (true) {
    for (var typeSpecName in typeSpecs) {
      if (has(typeSpecs, typeSpecName)) {
        var error;
        // Prop type validation may throw. In case they do, we don't want to
        // fail the render phase where it didn't fail before. So we log it.
        // After these have been cleaned up, we'll let them throw.
        try {
          // This is intentionally an invariant that gets caught. It's the same
          // behavior as without this statement except with a better message.
          if (typeof typeSpecs[typeSpecName] !== 'function') {
            var err = Error(
              (componentName || 'React class') + ': ' + location + ' type `' + typeSpecName + '` is invalid; ' +
              'it must be a function, usually from the `prop-types` package, but received `' + typeof typeSpecs[typeSpecName] + '`.' +
              'This often happens because of typos such as `PropTypes.function` instead of `PropTypes.func`.'
            );
            err.name = 'Invariant Violation';
            throw err;
          }
          error = typeSpecs[typeSpecName](values, typeSpecName, componentName, location, null, ReactPropTypesSecret);
        } catch (ex) {
          error = ex;
        }
        if (error && !(error instanceof Error)) {
          printWarning(
            (componentName || 'React class') + ': type specification of ' +
            location + ' `' + typeSpecName + '` is invalid; the type checker ' +
            'function must return `null` or an `Error` but returned a ' + typeof error + '. ' +
            'You may have forgotten to pass an argument to the type checker ' +
            'creator (arrayOf, instanceOf, objectOf, oneOf, oneOfType, and ' +
            'shape all require an argument).'
          );
        }
        if (error instanceof Error && !(error.message in loggedTypeFailures)) {
          // Only monitor this failure once because there tends to be a lot of the
          // same error.
          loggedTypeFailures[error.message] = true;

          var stack = getStack ? getStack() : '';

          printWarning(
            'Failed ' + location + ' type: ' + error.message + (stack != null ? stack : '')
          );
        }
      }
    }
  }
}

/**
 * Resets warning cache when testing.
 *
 * @private
 */
checkPropTypes.resetWarningCache = function() {
  if (true) {
    loggedTypeFailures = {};
  }
}

module.exports = checkPropTypes;


/***/ }),

/***/ "./node_modules/prop-types/factoryWithTypeCheckers.js":
/*!************************************************************!*\
  !*** ./node_modules/prop-types/factoryWithTypeCheckers.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __nested_webpack_require_15184__) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactIs = __nested_webpack_require_15184__(/*! react-is */ "./node_modules/react-is/index.js");
var assign = __nested_webpack_require_15184__(/*! object-assign */ "./node_modules/object-assign/index.js");

var ReactPropTypesSecret = __nested_webpack_require_15184__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
var has = __nested_webpack_require_15184__(/*! ./lib/has */ "./node_modules/prop-types/lib/has.js");
var checkPropTypes = __nested_webpack_require_15184__(/*! ./checkPropTypes */ "./node_modules/prop-types/checkPropTypes.js");

var printWarning = function() {};

if (true) {
  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) {}
  };
}

function emptyFunctionThatReturnsNull() {
  return null;
}

module.exports = function(isValidElement, throwOnDirectAccess) {
  /* global Symbol */
  var ITERATOR_SYMBOL = typeof Symbol === 'function' && Symbol.iterator;
  var FAUX_ITERATOR_SYMBOL = '@@iterator'; // Before Symbol spec.

  /**
   * Returns the iterator method function contained on the iterable object.
   *
   * Be sure to invoke the function with the iterable as context:
   *
   *     var iteratorFn = getIteratorFn(myIterable);
   *     if (iteratorFn) {
   *       var iterator = iteratorFn.call(myIterable);
   *       ...
   *     }
   *
   * @param {?object} maybeIterable
   * @return {?function}
   */
  function getIteratorFn(maybeIterable) {
    var iteratorFn = maybeIterable && (ITERATOR_SYMBOL && maybeIterable[ITERATOR_SYMBOL] || maybeIterable[FAUX_ITERATOR_SYMBOL]);
    if (typeof iteratorFn === 'function') {
      return iteratorFn;
    }
  }

  /**
   * Collection of methods that allow declaration and validation of props that are
   * supplied to React components. Example usage:
   *
   *   var Props = require('ReactPropTypes');
   *   var MyArticle = React.createClass({
   *     propTypes: {
   *       // An optional string prop named "description".
   *       description: Props.string,
   *
   *       // A required enum prop named "category".
   *       category: Props.oneOf(['News','Photos']).isRequired,
   *
   *       // A prop named "dialog" that requires an instance of Dialog.
   *       dialog: Props.instanceOf(Dialog).isRequired
   *     },
   *     render: function() { ... }
   *   });
   *
   * A more formal specification of how these methods are used:
   *
   *   type := array|bool|func|object|number|string|oneOf([...])|instanceOf(...)
   *   decl := ReactPropTypes.{type}(.isRequired)?
   *
   * Each and every declaration produces a function with the same signature. This
   * allows the creation of custom validation functions. For example:
   *
   *  var MyLink = React.createClass({
   *    propTypes: {
   *      // An optional string or URI prop named "href".
   *      href: function(props, propName, componentName) {
   *        var propValue = props[propName];
   *        if (propValue != null && typeof propValue !== 'string' &&
   *            !(propValue instanceof URI)) {
   *          return new Error(
   *            'Expected a string or an URI for ' + propName + ' in ' +
   *            componentName
   *          );
   *        }
   *      }
   *    },
   *    render: function() {...}
   *  });
   *
   * @internal
   */

  var ANONYMOUS = '<<anonymous>>';

  // Important!
  // Keep this list in sync with production version in `./factoryWithThrowingShims.js`.
  var ReactPropTypes = {
    array: createPrimitiveTypeChecker('array'),
    bigint: createPrimitiveTypeChecker('bigint'),
    bool: createPrimitiveTypeChecker('boolean'),
    func: createPrimitiveTypeChecker('function'),
    number: createPrimitiveTypeChecker('number'),
    object: createPrimitiveTypeChecker('object'),
    string: createPrimitiveTypeChecker('string'),
    symbol: createPrimitiveTypeChecker('symbol'),

    any: createAnyTypeChecker(),
    arrayOf: createArrayOfTypeChecker,
    element: createElementTypeChecker(),
    elementType: createElementTypeTypeChecker(),
    instanceOf: createInstanceTypeChecker,
    node: createNodeChecker(),
    objectOf: createObjectOfTypeChecker,
    oneOf: createEnumTypeChecker,
    oneOfType: createUnionTypeChecker,
    shape: createShapeTypeChecker,
    exact: createStrictShapeTypeChecker,
  };

  /**
   * inlined Object.is polyfill to avoid requiring consumers ship their own
   * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/is
   */
  /*eslint-disable no-self-compare*/
  function is(x, y) {
    // SameValue algorithm
    if (x === y) {
      // Steps 1-5, 7-10
      // Steps 6.b-6.e: +0 != -0
      return x !== 0 || 1 / x === 1 / y;
    } else {
      // Step 6.a: NaN == NaN
      return x !== x && y !== y;
    }
  }
  /*eslint-enable no-self-compare*/

  /**
   * We use an Error-like object for backward compatibility as people may call
   * PropTypes directly and inspect their output. However, we don't use real
   * Errors anymore. We don't inspect their stack anyway, and creating them
   * is prohibitively expensive if they are created too often, such as what
   * happens in oneOfType() for any type before the one that matched.
   */
  function PropTypeError(message, data) {
    this.message = message;
    this.data = data && typeof data === 'object' ? data: {};
    this.stack = '';
  }
  // Make `instanceof Error` still work for returned errors.
  PropTypeError.prototype = Error.prototype;

  function createChainableTypeChecker(validate) {
    if (true) {
      var manualPropTypeCallCache = {};
      var manualPropTypeWarningCount = 0;
    }
    function checkType(isRequired, props, propName, componentName, location, propFullName, secret) {
      componentName = componentName || ANONYMOUS;
      propFullName = propFullName || propName;

      if (secret !== ReactPropTypesSecret) {
        if (throwOnDirectAccess) {
          // New behavior only for users of `prop-types` package
          var err = new Error(
            'Calling PropTypes validators directly is not supported by the `prop-types` package. ' +
            'Use `PropTypes.checkPropTypes()` to call them. ' +
            'Read more at http://fb.me/use-check-prop-types'
          );
          err.name = 'Invariant Violation';
          throw err;
        } else if (  true && typeof console !== 'undefined') {
          // Old behavior for people using React.PropTypes
          var cacheKey = componentName + ':' + propName;
          if (
            !manualPropTypeCallCache[cacheKey] &&
            // Avoid spamming the console because they are often not actionable except for lib authors
            manualPropTypeWarningCount < 3
          ) {
            printWarning(
              'You are manually calling a React.PropTypes validation ' +
              'function for the `' + propFullName + '` prop on `' + componentName + '`. This is deprecated ' +
              'and will throw in the standalone `prop-types` package. ' +
              'You may be seeing this warning due to a third-party PropTypes ' +
              'library. See https://fb.me/react-warning-dont-call-proptypes ' + 'for details.'
            );
            manualPropTypeCallCache[cacheKey] = true;
            manualPropTypeWarningCount++;
          }
        }
      }
      if (props[propName] == null) {
        if (isRequired) {
          if (props[propName] === null) {
            return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required ' + ('in `' + componentName + '`, but its value is `null`.'));
          }
          return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required in ' + ('`' + componentName + '`, but its value is `undefined`.'));
        }
        return null;
      } else {
        return validate(props, propName, componentName, location, propFullName);
      }
    }

    var chainedCheckType = checkType.bind(null, false);
    chainedCheckType.isRequired = checkType.bind(null, true);

    return chainedCheckType;
  }

  function createPrimitiveTypeChecker(expectedType) {
    function validate(props, propName, componentName, location, propFullName, secret) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== expectedType) {
        // `propValue` being instance of, say, date/regexp, pass the 'object'
        // check, but we can offer a more precise error message here rather than
        // 'of type `object`'.
        var preciseType = getPreciseType(propValue);

        return new PropTypeError(
          'Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + preciseType + '` supplied to `' + componentName + '`, expected ') + ('`' + expectedType + '`.'),
          {expectedType: expectedType}
        );
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createAnyTypeChecker() {
    return createChainableTypeChecker(emptyFunctionThatReturnsNull);
  }

  function createArrayOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside arrayOf.');
      }
      var propValue = props[propName];
      if (!Array.isArray(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an array.'));
      }
      for (var i = 0; i < propValue.length; i++) {
        var error = typeChecker(propValue, i, componentName, location, propFullName + '[' + i + ']', ReactPropTypesSecret);
        if (error instanceof Error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!isValidElement(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!ReactIs.isValidElementType(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement type.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createInstanceTypeChecker(expectedClass) {
    function validate(props, propName, componentName, location, propFullName) {
      if (!(props[propName] instanceof expectedClass)) {
        var expectedClassName = expectedClass.name || ANONYMOUS;
        var actualClassName = getClassName(props[propName]);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + actualClassName + '` supplied to `' + componentName + '`, expected ') + ('instance of `' + expectedClassName + '`.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createEnumTypeChecker(expectedValues) {
    if (!Array.isArray(expectedValues)) {
      if (true) {
        if (arguments.length > 1) {
          printWarning(
            'Invalid arguments supplied to oneOf, expected an array, got ' + arguments.length + ' arguments. ' +
            'A common mistake is to write oneOf(x, y, z) instead of oneOf([x, y, z]).'
          );
        } else {
          printWarning('Invalid argument supplied to oneOf, expected an array.');
        }
      }
      return emptyFunctionThatReturnsNull;
    }

    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      for (var i = 0; i < expectedValues.length; i++) {
        if (is(propValue, expectedValues[i])) {
          return null;
        }
      }

      var valuesString = JSON.stringify(expectedValues, function replacer(key, value) {
        var type = getPreciseType(value);
        if (type === 'symbol') {
          return String(value);
        }
        return value;
      });
      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of value `' + String(propValue) + '` ' + ('supplied to `' + componentName + '`, expected one of ' + valuesString + '.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createObjectOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside objectOf.');
      }
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an object.'));
      }
      for (var key in propValue) {
        if (has(propValue, key)) {
          var error = typeChecker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
          if (error instanceof Error) {
            return error;
          }
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createUnionTypeChecker(arrayOfTypeCheckers) {
    if (!Array.isArray(arrayOfTypeCheckers)) {
        true ? printWarning('Invalid argument supplied to oneOfType, expected an instance of array.') : 0;
      return emptyFunctionThatReturnsNull;
    }

    for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
      var checker = arrayOfTypeCheckers[i];
      if (typeof checker !== 'function') {
        printWarning(
          'Invalid argument supplied to oneOfType. Expected an array of check functions, but ' +
          'received ' + getPostfixForTypeWarning(checker) + ' at index ' + i + '.'
        );
        return emptyFunctionThatReturnsNull;
      }
    }

    function validate(props, propName, componentName, location, propFullName) {
      var expectedTypes = [];
      for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
        var checker = arrayOfTypeCheckers[i];
        var checkerResult = checker(props, propName, componentName, location, propFullName, ReactPropTypesSecret);
        if (checkerResult == null) {
          return null;
        }
        if (checkerResult.data && has(checkerResult.data, 'expectedType')) {
          expectedTypes.push(checkerResult.data.expectedType);
        }
      }
      var expectedTypesMessage = (expectedTypes.length > 0) ? ', expected one of type [' + expectedTypes.join(', ') + ']': '';
      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`' + expectedTypesMessage + '.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createNodeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      if (!isNode(props[propName])) {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`, expected a ReactNode.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function invalidValidatorError(componentName, location, propFullName, key, type) {
    return new PropTypeError(
      (componentName || 'React class') + ': ' + location + ' type `' + propFullName + '.' + key + '` is invalid; ' +
      'it must be a function, usually from the `prop-types` package, but received `' + type + '`.'
    );
  }

  function createShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      for (var key in shapeTypes) {
        var checker = shapeTypes[key];
        if (typeof checker !== 'function') {
          return invalidValidatorError(componentName, location, propFullName, key, getPreciseType(checker));
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createStrictShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      // We need to check all keys in case some are required but missing from props.
      var allKeys = assign({}, props[propName], shapeTypes);
      for (var key in allKeys) {
        var checker = shapeTypes[key];
        if (has(shapeTypes, key) && typeof checker !== 'function') {
          return invalidValidatorError(componentName, location, propFullName, key, getPreciseType(checker));
        }
        if (!checker) {
          return new PropTypeError(
            'Invalid ' + location + ' `' + propFullName + '` key `' + key + '` supplied to `' + componentName + '`.' +
            '\nBad object: ' + JSON.stringify(props[propName], null, '  ') +
            '\nValid keys: ' + JSON.stringify(Object.keys(shapeTypes), null, '  ')
          );
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }

    return createChainableTypeChecker(validate);
  }

  function isNode(propValue) {
    switch (typeof propValue) {
      case 'number':
      case 'string':
      case 'undefined':
        return true;
      case 'boolean':
        return !propValue;
      case 'object':
        if (Array.isArray(propValue)) {
          return propValue.every(isNode);
        }
        if (propValue === null || isValidElement(propValue)) {
          return true;
        }

        var iteratorFn = getIteratorFn(propValue);
        if (iteratorFn) {
          var iterator = iteratorFn.call(propValue);
          var step;
          if (iteratorFn !== propValue.entries) {
            while (!(step = iterator.next()).done) {
              if (!isNode(step.value)) {
                return false;
              }
            }
          } else {
            // Iterator will provide entry [k,v] tuples rather than values.
            while (!(step = iterator.next()).done) {
              var entry = step.value;
              if (entry) {
                if (!isNode(entry[1])) {
                  return false;
                }
              }
            }
          }
        } else {
          return false;
        }

        return true;
      default:
        return false;
    }
  }

  function isSymbol(propType, propValue) {
    // Native Symbol.
    if (propType === 'symbol') {
      return true;
    }

    // falsy value can't be a Symbol
    if (!propValue) {
      return false;
    }

    // 19.4.3.5 Symbol.prototype[@@toStringTag] === 'Symbol'
    if (propValue['@@toStringTag'] === 'Symbol') {
      return true;
    }

    // Fallback for non-spec compliant Symbols which are polyfilled.
    if (typeof Symbol === 'function' && propValue instanceof Symbol) {
      return true;
    }

    return false;
  }

  // Equivalent of `typeof` but with special handling for array and regexp.
  function getPropType(propValue) {
    var propType = typeof propValue;
    if (Array.isArray(propValue)) {
      return 'array';
    }
    if (propValue instanceof RegExp) {
      // Old webkits (at least until Android 4.0) return 'function' rather than
      // 'object' for typeof a RegExp. We'll normalize this here so that /bla/
      // passes PropTypes.object.
      return 'object';
    }
    if (isSymbol(propType, propValue)) {
      return 'symbol';
    }
    return propType;
  }

  // This handles more types than `getPropType`. Only used for error messages.
  // See `createPrimitiveTypeChecker`.
  function getPreciseType(propValue) {
    if (typeof propValue === 'undefined' || propValue === null) {
      return '' + propValue;
    }
    var propType = getPropType(propValue);
    if (propType === 'object') {
      if (propValue instanceof Date) {
        return 'date';
      } else if (propValue instanceof RegExp) {
        return 'regexp';
      }
    }
    return propType;
  }

  // Returns a string that is postfixed to a warning about an invalid type.
  // For example, "undefined" or "of type array"
  function getPostfixForTypeWarning(value) {
    var type = getPreciseType(value);
    switch (type) {
      case 'array':
      case 'object':
        return 'an ' + type;
      case 'boolean':
      case 'date':
      case 'regexp':
        return 'a ' + type;
      default:
        return type;
    }
  }

  // Returns class name of the object, if any.
  function getClassName(propValue) {
    if (!propValue.constructor || !propValue.constructor.name) {
      return ANONYMOUS;
    }
    return propValue.constructor.name;
  }

  ReactPropTypes.checkPropTypes = checkPropTypes;
  ReactPropTypes.resetWarningCache = checkPropTypes.resetWarningCache;
  ReactPropTypes.PropTypes = ReactPropTypes;

  return ReactPropTypes;
};


/***/ }),

/***/ "./node_modules/prop-types/index.js":
/*!******************************************!*\
  !*** ./node_modules/prop-types/index.js ***!
  \******************************************/
/***/ ((module, __unused_webpack_exports, __nested_webpack_require_38303__) => {

/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

if (true) {
  var ReactIs = __nested_webpack_require_38303__(/*! react-is */ "./node_modules/react-is/index.js");

  // By explicitly using `prop-types` you are opting into new development behavior.
  // http://fb.me/prop-types-in-prod
  var throwOnDirectAccess = true;
  module.exports = __nested_webpack_require_38303__(/*! ./factoryWithTypeCheckers */ "./node_modules/prop-types/factoryWithTypeCheckers.js")(ReactIs.isElement, throwOnDirectAccess);
} else {}


/***/ }),

/***/ "./node_modules/prop-types/lib/ReactPropTypesSecret.js":
/*!*************************************************************!*\
  !*** ./node_modules/prop-types/lib/ReactPropTypesSecret.js ***!
  \*************************************************************/
/***/ ((module) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactPropTypesSecret = 'SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED';

module.exports = ReactPropTypesSecret;


/***/ }),

/***/ "./node_modules/prop-types/lib/has.js":
/*!********************************************!*\
  !*** ./node_modules/prop-types/lib/has.js ***!
  \********************************************/
/***/ ((module) => {

module.exports = Function.call.bind(Object.prototype.hasOwnProperty);


/***/ }),

/***/ "./node_modules/react-is/cjs/react-is.development.js":
/*!***********************************************************!*\
  !*** ./node_modules/react-is/cjs/react-is.development.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
/** @license React v16.13.1
 * react-is.development.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */





if (true) {
  (function() {
'use strict';

// The Symbol used to tag the ReactElement-like types. If there is no native Symbol
// nor polyfill, then a plain number is used for performance.
var hasSymbol = typeof Symbol === 'function' && Symbol.for;
var REACT_ELEMENT_TYPE = hasSymbol ? Symbol.for('react.element') : 0xeac7;
var REACT_PORTAL_TYPE = hasSymbol ? Symbol.for('react.portal') : 0xeaca;
var REACT_FRAGMENT_TYPE = hasSymbol ? Symbol.for('react.fragment') : 0xeacb;
var REACT_STRICT_MODE_TYPE = hasSymbol ? Symbol.for('react.strict_mode') : 0xeacc;
var REACT_PROFILER_TYPE = hasSymbol ? Symbol.for('react.profiler') : 0xead2;
var REACT_PROVIDER_TYPE = hasSymbol ? Symbol.for('react.provider') : 0xeacd;
var REACT_CONTEXT_TYPE = hasSymbol ? Symbol.for('react.context') : 0xeace; // TODO: We don't use AsyncMode or ConcurrentMode anymore. They were temporary
// (unstable) APIs that have been removed. Can we remove the symbols?

var REACT_ASYNC_MODE_TYPE = hasSymbol ? Symbol.for('react.async_mode') : 0xeacf;
var REACT_CONCURRENT_MODE_TYPE = hasSymbol ? Symbol.for('react.concurrent_mode') : 0xeacf;
var REACT_FORWARD_REF_TYPE = hasSymbol ? Symbol.for('react.forward_ref') : 0xead0;
var REACT_SUSPENSE_TYPE = hasSymbol ? Symbol.for('react.suspense') : 0xead1;
var REACT_SUSPENSE_LIST_TYPE = hasSymbol ? Symbol.for('react.suspense_list') : 0xead8;
var REACT_MEMO_TYPE = hasSymbol ? Symbol.for('react.memo') : 0xead3;
var REACT_LAZY_TYPE = hasSymbol ? Symbol.for('react.lazy') : 0xead4;
var REACT_BLOCK_TYPE = hasSymbol ? Symbol.for('react.block') : 0xead9;
var REACT_FUNDAMENTAL_TYPE = hasSymbol ? Symbol.for('react.fundamental') : 0xead5;
var REACT_RESPONDER_TYPE = hasSymbol ? Symbol.for('react.responder') : 0xead6;
var REACT_SCOPE_TYPE = hasSymbol ? Symbol.for('react.scope') : 0xead7;

function isValidElementType(type) {
  return typeof type === 'string' || typeof type === 'function' || // Note: its typeof might be other than 'symbol' or 'number' if it's a polyfill.
  type === REACT_FRAGMENT_TYPE || type === REACT_CONCURRENT_MODE_TYPE || type === REACT_PROFILER_TYPE || type === REACT_STRICT_MODE_TYPE || type === REACT_SUSPENSE_TYPE || type === REACT_SUSPENSE_LIST_TYPE || typeof type === 'object' && type !== null && (type.$$typeof === REACT_LAZY_TYPE || type.$$typeof === REACT_MEMO_TYPE || type.$$typeof === REACT_PROVIDER_TYPE || type.$$typeof === REACT_CONTEXT_TYPE || type.$$typeof === REACT_FORWARD_REF_TYPE || type.$$typeof === REACT_FUNDAMENTAL_TYPE || type.$$typeof === REACT_RESPONDER_TYPE || type.$$typeof === REACT_SCOPE_TYPE || type.$$typeof === REACT_BLOCK_TYPE);
}

function typeOf(object) {
  if (typeof object === 'object' && object !== null) {
    var $$typeof = object.$$typeof;

    switch ($$typeof) {
      case REACT_ELEMENT_TYPE:
        var type = object.type;

        switch (type) {
          case REACT_ASYNC_MODE_TYPE:
          case REACT_CONCURRENT_MODE_TYPE:
          case REACT_FRAGMENT_TYPE:
          case REACT_PROFILER_TYPE:
          case REACT_STRICT_MODE_TYPE:
          case REACT_SUSPENSE_TYPE:
            return type;

          default:
            var $$typeofType = type && type.$$typeof;

            switch ($$typeofType) {
              case REACT_CONTEXT_TYPE:
              case REACT_FORWARD_REF_TYPE:
              case REACT_LAZY_TYPE:
              case REACT_MEMO_TYPE:
              case REACT_PROVIDER_TYPE:
                return $$typeofType;

              default:
                return $$typeof;
            }

        }

      case REACT_PORTAL_TYPE:
        return $$typeof;
    }
  }

  return undefined;
} // AsyncMode is deprecated along with isAsyncMode

var AsyncMode = REACT_ASYNC_MODE_TYPE;
var ConcurrentMode = REACT_CONCURRENT_MODE_TYPE;
var ContextConsumer = REACT_CONTEXT_TYPE;
var ContextProvider = REACT_PROVIDER_TYPE;
var Element = REACT_ELEMENT_TYPE;
var ForwardRef = REACT_FORWARD_REF_TYPE;
var Fragment = REACT_FRAGMENT_TYPE;
var Lazy = REACT_LAZY_TYPE;
var Memo = REACT_MEMO_TYPE;
var Portal = REACT_PORTAL_TYPE;
var Profiler = REACT_PROFILER_TYPE;
var StrictMode = REACT_STRICT_MODE_TYPE;
var Suspense = REACT_SUSPENSE_TYPE;
var hasWarnedAboutDeprecatedIsAsyncMode = false; // AsyncMode should be deprecated

function isAsyncMode(object) {
  {
    if (!hasWarnedAboutDeprecatedIsAsyncMode) {
      hasWarnedAboutDeprecatedIsAsyncMode = true; // Using console['warn'] to evade Babel and ESLint

      console['warn']('The ReactIs.isAsyncMode() alias has been deprecated, ' + 'and will be removed in React 17+. Update your code to use ' + 'ReactIs.isConcurrentMode() instead. It has the exact same API.');
    }
  }

  return isConcurrentMode(object) || typeOf(object) === REACT_ASYNC_MODE_TYPE;
}
function isConcurrentMode(object) {
  return typeOf(object) === REACT_CONCURRENT_MODE_TYPE;
}
function isContextConsumer(object) {
  return typeOf(object) === REACT_CONTEXT_TYPE;
}
function isContextProvider(object) {
  return typeOf(object) === REACT_PROVIDER_TYPE;
}
function isElement(object) {
  return typeof object === 'object' && object !== null && object.$$typeof === REACT_ELEMENT_TYPE;
}
function isForwardRef(object) {
  return typeOf(object) === REACT_FORWARD_REF_TYPE;
}
function isFragment(object) {
  return typeOf(object) === REACT_FRAGMENT_TYPE;
}
function isLazy(object) {
  return typeOf(object) === REACT_LAZY_TYPE;
}
function isMemo(object) {
  return typeOf(object) === REACT_MEMO_TYPE;
}
function isPortal(object) {
  return typeOf(object) === REACT_PORTAL_TYPE;
}
function isProfiler(object) {
  return typeOf(object) === REACT_PROFILER_TYPE;
}
function isStrictMode(object) {
  return typeOf(object) === REACT_STRICT_MODE_TYPE;
}
function isSuspense(object) {
  return typeOf(object) === REACT_SUSPENSE_TYPE;
}

exports.AsyncMode = AsyncMode;
exports.ConcurrentMode = ConcurrentMode;
exports.ContextConsumer = ContextConsumer;
exports.ContextProvider = ContextProvider;
exports.Element = Element;
exports.ForwardRef = ForwardRef;
exports.Fragment = Fragment;
exports.Lazy = Lazy;
exports.Memo = Memo;
exports.Portal = Portal;
exports.Profiler = Profiler;
exports.StrictMode = StrictMode;
exports.Suspense = Suspense;
exports.isAsyncMode = isAsyncMode;
exports.isConcurrentMode = isConcurrentMode;
exports.isContextConsumer = isContextConsumer;
exports.isContextProvider = isContextProvider;
exports.isElement = isElement;
exports.isForwardRef = isForwardRef;
exports.isFragment = isFragment;
exports.isLazy = isLazy;
exports.isMemo = isMemo;
exports.isPortal = isPortal;
exports.isProfiler = isProfiler;
exports.isStrictMode = isStrictMode;
exports.isSuspense = isSuspense;
exports.isValidElementType = isValidElementType;
exports.typeOf = typeOf;
  })();
}


/***/ }),

/***/ "./node_modules/react-is/index.js":
/*!****************************************!*\
  !*** ./node_modules/react-is/index.js ***!
  \****************************************/
/***/ ((module, __unused_webpack_exports, __nested_webpack_require_47361__) => {

"use strict";


if (false) {} else {
  module.exports = __nested_webpack_require_47361__(/*! ./cjs/react-is.development.js */ "./node_modules/react-is/cjs/react-is.development.js");
}


/***/ }),

/***/ "./node_modules/shallow-equal/dist/index.esm.js":
/*!******************************************************!*\
  !*** ./node_modules/shallow-equal/dist/index.esm.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __nested_webpack_exports__, __nested_webpack_require_47861__) => {

"use strict";
__nested_webpack_require_47861__.r(__nested_webpack_exports__);
/* harmony export */ __nested_webpack_require_47861__.d(__nested_webpack_exports__, {
/* harmony export */   "shallowEqualArrays": () => (/* binding */ shallowEqualArrays),
/* harmony export */   "shallowEqualObjects": () => (/* binding */ shallowEqualObjects)
/* harmony export */ });
function shallowEqualObjects(objA, objB) {
  if (objA === objB) {
    return true;
  }

  if (!objA || !objB) {
    return false;
  }

  var aKeys = Object.keys(objA);
  var bKeys = Object.keys(objB);
  var len = aKeys.length;

  if (bKeys.length !== len) {
    return false;
  }

  for (var i = 0; i < len; i++) {
    var key = aKeys[i];

    if (objA[key] !== objB[key] || !Object.prototype.hasOwnProperty.call(objB, key)) {
      return false;
    }
  }

  return true;
}

function shallowEqualArrays(arrA, arrB) {
  if (arrA === arrB) {
    return true;
  }

  if (!arrA || !arrB) {
    return false;
  }

  var len = arrA.length;

  if (arrB.length !== len) {
    return false;
  }

  for (var i = 0; i < len; i++) {
    if (arrA[i] !== arrB[i]) {
      return false;
    }
  }

  return true;
}




/***/ }),

/***/ "./src/Component.ts":
/*!**************************!*\
  !*** ./src/Component.ts ***!
  \**************************/
/***/ (function(__unused_webpack_module, exports, __nested_webpack_require_49201__) {

"use strict";

var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
var useMediaQuery_1 = __importDefault(__nested_webpack_require_49201__(/*! ./useMediaQuery */ "./src/useMediaQuery.ts"));
// ReactNode and ReactElement typings are a little funky for functional components, so the ReactElement cast is needed on the return
var MediaQuery = function (_a) {
    var children = _a.children, device = _a.device, onChange = _a.onChange, settings = __rest(_a, ["children", "device", "onChange"]);
    var matches = (0, useMediaQuery_1.default)(settings, device, onChange);
    if (typeof children === 'function') {
        return children(matches);
    }
    return matches ? children : null;
};
exports["default"] = MediaQuery;


/***/ }),

/***/ "./src/Context.ts":
/*!************************!*\
  !*** ./src/Context.ts ***!
  \************************/
/***/ ((__unused_webpack_module, exports, __nested_webpack_require_50747__) => {

"use strict";

Object.defineProperty(exports, "__esModule", ({ value: true }));
var react_1 = __nested_webpack_require_50747__(/*! react */ "react");
var Context = (0, react_1.createContext)(undefined);
exports["default"] = Context;


/***/ }),

/***/ "./src/index.ts":
/*!**********************!*\
  !*** ./src/index.ts ***!
  \**********************/
/***/ (function(__unused_webpack_module, exports, __nested_webpack_require_51164__) {

"use strict";

var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.Context = exports.toQuery = exports.useMediaQuery = exports["default"] = void 0;
var useMediaQuery_1 = __importDefault(__nested_webpack_require_51164__(/*! ./useMediaQuery */ "./src/useMediaQuery.ts"));
exports.useMediaQuery = useMediaQuery_1.default;
var Component_1 = __importDefault(__nested_webpack_require_51164__(/*! ./Component */ "./src/Component.ts"));
exports["default"] = Component_1.default;
var toQuery_1 = __importDefault(__nested_webpack_require_51164__(/*! ./toQuery */ "./src/toQuery.ts"));
exports.toQuery = toQuery_1.default;
var Context_1 = __importDefault(__nested_webpack_require_51164__(/*! ./Context */ "./src/Context.ts"));
exports.Context = Context_1.default;


/***/ }),

/***/ "./src/mediaQuery.ts":
/*!***************************!*\
  !*** ./src/mediaQuery.ts ***!
  \***************************/
/***/ (function(__unused_webpack_module, exports, __nested_webpack_require_52239__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
var prop_types_1 = __importDefault(__nested_webpack_require_52239__(/*! prop-types */ "./node_modules/prop-types/index.js"));
var stringOrNumber = prop_types_1.default.oneOfType([
    prop_types_1.default.string,
    prop_types_1.default.number
]);
// media types
var types = {
    all: prop_types_1.default.bool,
    grid: prop_types_1.default.bool,
    aural: prop_types_1.default.bool,
    braille: prop_types_1.default.bool,
    handheld: prop_types_1.default.bool,
    print: prop_types_1.default.bool,
    projection: prop_types_1.default.bool,
    screen: prop_types_1.default.bool,
    tty: prop_types_1.default.bool,
    tv: prop_types_1.default.bool,
    embossed: prop_types_1.default.bool
};
// properties that match media queries
var matchers = {
    orientation: prop_types_1.default.oneOf([
        'portrait',
        'landscape'
    ]),
    scan: prop_types_1.default.oneOf([
        'progressive',
        'interlace'
    ]),
    aspectRatio: prop_types_1.default.string,
    deviceAspectRatio: prop_types_1.default.string,
    height: stringOrNumber,
    deviceHeight: stringOrNumber,
    width: stringOrNumber,
    deviceWidth: stringOrNumber,
    color: prop_types_1.default.bool,
    colorIndex: prop_types_1.default.bool,
    monochrome: prop_types_1.default.bool,
    resolution: stringOrNumber,
    type: Object.keys(types)
};
// eslint-disable-next-line @typescript-eslint/no-unused-vars
var type = matchers.type, featureMatchers = __rest(matchers
// media features
, ["type"]);
// media features
var features = __assign({ minAspectRatio: prop_types_1.default.string, maxAspectRatio: prop_types_1.default.string, minDeviceAspectRatio: prop_types_1.default.string, maxDeviceAspectRatio: prop_types_1.default.string, minHeight: stringOrNumber, maxHeight: stringOrNumber, minDeviceHeight: stringOrNumber, maxDeviceHeight: stringOrNumber, minWidth: stringOrNumber, maxWidth: stringOrNumber, minDeviceWidth: stringOrNumber, maxDeviceWidth: stringOrNumber, minColor: prop_types_1.default.number, maxColor: prop_types_1.default.number, minColorIndex: prop_types_1.default.number, maxColorIndex: prop_types_1.default.number, minMonochrome: prop_types_1.default.number, maxMonochrome: prop_types_1.default.number, minResolution: stringOrNumber, maxResolution: stringOrNumber }, featureMatchers);
var all = __assign(__assign({}, types), features);
exports["default"] = {
    all: all,
    types: types,
    matchers: matchers,
    features: features
};


/***/ }),

/***/ "./src/toQuery.ts":
/*!************************!*\
  !*** ./src/toQuery.ts ***!
  \************************/
/***/ (function(__unused_webpack_module, exports, __nested_webpack_require_55990__) {

"use strict";

var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
var hyphenate_style_name_1 = __importDefault(__nested_webpack_require_55990__(/*! hyphenate-style-name */ "./node_modules/hyphenate-style-name/index.js"));
var mediaQuery_1 = __importDefault(__nested_webpack_require_55990__(/*! ./mediaQuery */ "./src/mediaQuery.ts"));
var negate = function (cond) { return "not ".concat(cond); };
var keyVal = function (k, v) {
    var realKey = (0, hyphenate_style_name_1.default)(k);
    // px shorthand
    if (typeof v === 'number') {
        v = "".concat(v, "px");
    }
    if (v === true) {
        return realKey;
    }
    if (v === false) {
        return negate(realKey);
    }
    return "(".concat(realKey, ": ").concat(v, ")");
};
var join = function (conds) { return conds.join(' and '); };
var toQuery = function (obj) {
    var rules = [];
    Object.keys(mediaQuery_1.default.all).forEach(function (k) {
        var v = obj[k];
        if (v != null) {
            rules.push(keyVal(k, v));
        }
    });
    return join(rules);
};
exports["default"] = toQuery;


/***/ }),

/***/ "./src/useMediaQuery.ts":
/*!******************************!*\
  !*** ./src/useMediaQuery.ts ***!
  \******************************/
/***/ (function(__unused_webpack_module, exports, __nested_webpack_require_57428__) {

"use strict";

var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
var react_1 = __nested_webpack_require_57428__(/*! react */ "react");
var matchmediaquery_1 = __importDefault(__nested_webpack_require_57428__(/*! matchmediaquery */ "./node_modules/matchmediaquery/index.js"));
var hyphenate_style_name_1 = __importDefault(__nested_webpack_require_57428__(/*! hyphenate-style-name */ "./node_modules/hyphenate-style-name/index.js"));
var shallow_equal_1 = __nested_webpack_require_57428__(/*! shallow-equal */ "./node_modules/shallow-equal/dist/index.esm.js");
var toQuery_1 = __importDefault(__nested_webpack_require_57428__(/*! ./toQuery */ "./src/toQuery.ts"));
var Context_1 = __importDefault(__nested_webpack_require_57428__(/*! ./Context */ "./src/Context.ts"));
var makeQuery = function (settings) { return settings.query || (0, toQuery_1.default)(settings); };
var hyphenateKeys = function (obj) {
    if (!obj)
        return undefined;
    var keys = Object.keys(obj);
    return keys.reduce(function (result, key) {
        result[(0, hyphenate_style_name_1.default)(key)] = obj[key];
        return result;
    }, {});
};
var useIsUpdate = function () {
    var ref = (0, react_1.useRef)(false);
    (0, react_1.useEffect)(function () {
        ref.current = true;
    }, []);
    return ref.current;
};
var useDevice = function (deviceFromProps) {
    var deviceFromContext = (0, react_1.useContext)(Context_1.default);
    var getDevice = function () {
        return hyphenateKeys(deviceFromProps) || hyphenateKeys(deviceFromContext);
    };
    var _a = (0, react_1.useState)(getDevice), device = _a[0], setDevice = _a[1];
    (0, react_1.useEffect)(function () {
        var newDevice = getDevice();
        if (!(0, shallow_equal_1.shallowEqualObjects)(device, newDevice)) {
            setDevice(newDevice);
        }
    }, [deviceFromProps, deviceFromContext]);
    return device;
};
var useQuery = function (settings) {
    var getQuery = function () { return makeQuery(settings); };
    var _a = (0, react_1.useState)(getQuery), query = _a[0], setQuery = _a[1];
    (0, react_1.useEffect)(function () {
        var newQuery = getQuery();
        if (query !== newQuery) {
            setQuery(newQuery);
        }
    }, [settings]);
    return query;
};
var useMatchMedia = function (query, device) {
    var getMatchMedia = function () { return (0, matchmediaquery_1.default)(query, device || {}, !!device); };
    var _a = (0, react_1.useState)(getMatchMedia), mq = _a[0], setMq = _a[1];
    var isUpdate = useIsUpdate();
    (0, react_1.useEffect)(function () {
        if (isUpdate) {
            // skip on mounting, it has already been set
            var newMq_1 = getMatchMedia();
            setMq(newMq_1);
            return function () {
                if (newMq_1) {
                    newMq_1.dispose();
                }
            };
        }
    }, [query, device]);
    return mq;
};
var useMatches = function (mediaQuery) {
    var _a = (0, react_1.useState)(mediaQuery.matches), matches = _a[0], setMatches = _a[1];
    (0, react_1.useEffect)(function () {
        var updateMatches = function (ev) {
            setMatches(ev.matches);
        };
        mediaQuery.addListener(updateMatches);
        setMatches(mediaQuery.matches);
        return function () {
            mediaQuery.removeListener(updateMatches);
        };
    }, [mediaQuery]);
    return matches;
};
var useMediaQuery = function (settings, device, onChange) {
    var deviceSettings = useDevice(device);
    var query = useQuery(settings);
    if (!query)
        throw new Error('Invalid or missing MediaQuery!');
    var mq = useMatchMedia(query, deviceSettings);
    var matches = useMatches(mq);
    var isUpdate = useIsUpdate();
    (0, react_1.useEffect)(function () {
        if (isUpdate && onChange) {
            onChange(matches);
        }
    }, [matches]);
    (0, react_1.useEffect)(function () { return function () {
        if (mq) {
            mq.dispose();
        }
    }; }, []);
    return matches;
};
exports["default"] = useMediaQuery;


/***/ }),

/***/ "react":
/*!**************************************************************************************!*\
  !*** external {"commonjs":"react","commonjs2":"react","amd":"react","root":"React"} ***!
  \**************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = __WEBPACK_EXTERNAL_MODULE_react__;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __nested_webpack_require_62230__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __nested_webpack_require_62230__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__nested_webpack_require_62230__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__nested_webpack_require_62230__.o(definition, key) && !__nested_webpack_require_62230__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__nested_webpack_require_62230__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__nested_webpack_require_62230__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __nested_webpack_exports__ = __nested_webpack_require_62230__("./src/index.ts");
/******/ 	
/******/ 	return __nested_webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=react-responsive.js.map

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

"use strict";
module.exports = window["React"];

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactDOM"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*************************!*\
  !*** ./src/frontend.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom */ "react-dom");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_Filter__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/Filter */ "./src/components/Filter.js");
/* harmony import */ var _components_FilterShop__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/FilterShop */ "./src/components/FilterShop.js");





const setupFilters = () => {
  // Handle Filter entries
  const filterDivs = document.querySelectorAll(".filter-entry");
  filterDivs.forEach(div => {
    let data = JSON.parse(div.dataset.initialData);
    console.log(data);
    const root = react_dom__WEBPACK_IMPORTED_MODULE_1___default().createRoot(div);
    root.render((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_Filter__WEBPACK_IMPORTED_MODULE_2__["default"], {
      ...data
    }));
    div.classList.remove("filter-entry");
  });

  // Handle FilterShop entries
  const filterShopDivs = document.querySelectorAll(".filter-entry-shop");
  filterShopDivs.forEach(div => {
    let data = JSON.parse(div.dataset.initialData);
    const root = react_dom__WEBPACK_IMPORTED_MODULE_1___default().createRoot(div);
    root.render((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_FilterShop__WEBPACK_IMPORTED_MODULE_3__["default"], {
      ...data
    }));
    div.classList.remove("filter-entry-shop");
  });
};
document.addEventListener('DOMContentLoaded', setupFilters);
})();

/******/ })()
;
//# sourceMappingURL=frontend.js.map