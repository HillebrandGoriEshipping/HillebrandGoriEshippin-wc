import config from "./conf.json" with { type: "json" };

export default {
  getApiUrl() {
    return config.apiUrl;
  },
    async validateApiKey(apiKey) {
        console.log("API Key:", apiKey);
        try {
            const response = await fetch(`${this.getApiUrl()}/package/get-sizes`, {
                method: "GET",
                headers: {
                "Content-Type": "application/json",
                "X-Auth-Token": apiKey,
                },
                redirect: "follow",
                mode: "no-cors",
                credentials: "include"
            });
            if (response.ok) {
                const data = await response.json();
                console.log(data);
                
                return data.valid;
            } else {
                console.error("Error validating API key:", response.statusText);
                return false;
            }
        } catch (error) {
            console.error("Error validating API key:", error);
            return false;
        }
    }
};
