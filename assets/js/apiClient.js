import config from "./conf.json" with { type: "json" };

export default {
  getApiUrl() {
    return config.apiUrl;
  },
  async get(url, urlParams, headers) {

   url = this.appendUrlParams(url, urlParams);

    return await fetch(
      url,
      {
        method: "GET",
        headers
      }
    );
  },
  async post(url, urlParams, data, headers) {

    if (urlParams) {
      const params = new URLSearchParams(urlParams);
      url += `?${params.toString()}`;
    }

    return await fetch(
      url,
      {
        method: "POST",
        body: JSON.stringify(data),
        headers
      }
    );
  },
  async validateApiKey(apiKey) {
    try {
      const response = await this.get(
        `${this.getApiUrl()}/package/get-sizes`,
        {
          nbBottles: 5
        },
        {
          "Content-Type": "application/json",
          "X-Auth-Token": apiKey,
        }
      );
      return !!response.ok;
    } catch (error) {
      console.error("Error validating API key:", error);
      return false;
    }
  },
  async getFromProxy(url, urlParams) {
    url = config.proxyApiUrl + url;
    try {
      const response = await this.get(
        url,
        urlParams,
        {
          "Content-Type": "application/json"
        }
      )
      return await response.json();
    } catch (error) {
      console.error("Error fetching data from proxy:", error);
      return null;
    }
  },
  async postProxy(url, urlParams, data) {
    url = config.proxyApiUrl + url;
    try {
      const response = await this.post(
        url,
        urlParams,
        data,
        {
          "Content-Type": "application/json"
        }
      )
      return await response.json();
    } catch (error) {
      console.error("Error posting data to proxy:", error);
      return null;
    }
  },
  appendUrlParams(url, urlParams) {

    if (url.includes('?')) {
      const queryString = url.split('?').pop();
      const formerParams = new URLSearchParams(queryString);
      if (!urlParams) {
        urlParams = {};
      }
      for (const [key, value] of formerParams.entries()) {
        console.log(key, value);
        urlParams[key] = value;
      }
    }

    if (urlParams) {
      const newUrlParams = new URLSearchParams(urlParams);
      const newQueryString = newUrlParams.toString();
      url += `?${newQueryString}`;
    }
    
    return url;
  }
};
