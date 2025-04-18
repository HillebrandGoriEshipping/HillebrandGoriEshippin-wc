import config from "./conf.json" with { type: "json" };

export default {
  getApiUrl() {
    return config.apiUrl;
  },
    async validateApiKey(apiKey) {
        console.log("API Key:", apiKey);
        try {
            const response = await fetch(`${this.getApiUrl()}/package/get-sizes?nbBottles=5`, {
                method: "GET",
                headers: {
                "Content-Type": "application/json",
                "X-Auth-Token": apiKey,
                },
            });
           return !!response.ok;
        } catch (error) {
            console.error("Error validating API key:", error);
            return false;
        }
    }
};
