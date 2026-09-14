document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("expenseForm");

    if (form)
    {
        form.addEventListener("submit", function (event) {

            const title = document.getElementById("expense_title").value.trim();
            const amount = document.getElementById("expense_amount").value;
            const date = document.getElementById("expense_date").value;
            const category = document.getElementById("category_id").value;

            if (
                title === "" ||
                amount === "" ||
                parseFloat(amount) <= 0 ||
                date === "" ||
                category === ""
            )
            {
                event.preventDefault();
                alert("Please fill all required expense fields correctly.");
            }

        });
    }

});

function confirmDelete()
{
    return confirm("Are you sure you want to delete this expense?");
}
