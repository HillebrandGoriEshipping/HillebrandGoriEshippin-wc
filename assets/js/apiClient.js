import config from "./config/config.json" with { type: "json" };

// window.hges is the global data object defined server-side

export default {
  getApiUrl(isProxy) {
    if (isProxy) {
      return config.proxyApiUrl;
    }

    return config.apiUrl;
  },
  async get(url, urlParams, headers, isProxy) {
    url = this.appendUrlParams(url, urlParams, isProxy);
    headers = this.prepareHeaders(headers);
    const method = "GET";

    try {
    const response = await fetch(url, { method, headers });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`HTTP ${response.status} - ${errorText}`);
    }

    return response.json();
    } catch (e) {
      throw new Error("Error in API Client : " + e.message);
    }
  },
  async post(url, urlParams, data, headers, isProxy) {
    url = this.appendUrlParams(url, urlParams, isProxy);
    headers = this.prepareHeaders(headers);
    const method = "POST";
    const body = JSON.stringify(data);

    try {
      const response = await fetch(url, { method, body, headers });

      return response.json();
    } catch (e) {
      throw new Error("Error in API Client : " + e.message);
    }
  },
  async patch(url, urlParams, data, headers, isProxy) {
    url = this.appendUrlParams(url, urlParams, isProxy);
    headers = this.prepareHeaders(headers);
    const method = "PATCH";
    const body = JSON.stringify(data);

    try {
      const response = await fetch(url, { method, body, headers });

      return response.json();
    } catch (e) {
      throw new Error("Error in API Client : " + e.message);
    }
  },
  async upload(url, urlParams, data, headers, isProxy) {
    url = this.appendUrlParams(url, urlParams, isProxy);

    if (!headers) {
      headers = {};
    }

    headers = this.prepareHeaders(headers);
    delete(headers["Content-Type"]);
    const method = "POST";
    const formData = new FormData();

    formData.append("type", data.type);
    formData.append("fileUpload", data.file);

    try {
      const response = await fetch(url, { method, body: formData, headers });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status} - ${errorText}`);
      }

      return response.json();
    } catch (e) {
      throw new Error("Error in API Client : " + e.message);
    }
  },
  async validateApiKey(apiKey) {
    try {
      const response = await this.get(
        `/package/get-sizes`,
        { nbBottles: 5 },
        { "X-Auth-Token": apiKey }
      );
      return !!response;
    } catch (error) {
      console.error("Error validating API key:", error);
      return false;
    }
  },
  /**
   * returns the URL with the query string generated from urlParams object
   * if the url already contains any url parameters, they'll be kept.
   *
   * @param {string} url
   * @param {object} urlParams
   * @returns string
   */
  appendUrlParams(url, urlParams, isProxy) {
    if (url.includes("?")) {
      const queryString = url.split("?").pop();
      const formerParams = new URLSearchParams(queryString);
      if (!urlParams) {
        urlParams = {};
      }
      for (const [key, value] of formerParams.entries()) {
        urlParams[key] = value;
      }
    }

    if (urlParams) {
      const newUrlParams = new URLSearchParams(urlParams);
      const newQueryString = newUrlParams.toString();
      url += `?${newQueryString}`;
    }

    if (!url.includes("http")) {
      url = this.getApiUrl(isProxy) + url;
    }

    return url;
  },
  /**
   * Adds default headers if not already set
   * The headers that are set explicitely in the headers param won't be overwritten.
   *
   * @param {object} headers
   * @returns object
   */
  prepareHeaders(headers) {
    headers = headers || {};

    const defaultHeaders = {
      "X-Auth-Token": hges.apiKey || "",
      "Content-Type": headers["Content-Type"] || "application/json",
    };

    for (const key in defaultHeaders) {
      if (typeof headers[key] === "undefined") {
        headers[key] = defaultHeaders[key];
      }
    }

    return headers;
  },
};
