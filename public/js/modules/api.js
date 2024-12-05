export class ApiService {
    static async get(url) {
        const response = await fetch(url, {
            method: "GET",
            headers: {
                Authorization: `Bearer ${getJwtToken()}`,
                Accept: "application/json",
            },
        });
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    }

    static async post(url, data) {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${getJwtToken()}`,
            },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    }

    static async put(url, data) {
        const response = await fetch(url, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${getJwtToken()}`,
            },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    }

    static async delete(url) {
        const response = await fetch(url, {
            method: "DELETE",
            headers: {
                Authorization: `Bearer ${getJwtToken()}`,
            },
        });
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    }
}
