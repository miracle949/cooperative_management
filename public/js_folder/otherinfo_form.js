
const members_type_category = document.getElementById("select_type");

members_type_category.addEventListener("input", () => {
    document.getElementById("members_category").textContent = members_type_category.value;
});
