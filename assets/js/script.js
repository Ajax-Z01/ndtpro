function confirmDelete() {
    var result = confirm("Are you sure you want to delete the profile picture?");
    if (result) {
        document.getElementById("deleteForm").submit();
    }
}
