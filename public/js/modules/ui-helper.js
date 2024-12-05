export class UIHelper {
    static showErrorToast(message) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: message,
        });
    }

    static showSuccessToast(message) {
        Swal.fire({
            icon: "success",
            title: "Success",
            text: message,
        });
    }
}
