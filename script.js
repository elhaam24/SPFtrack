const apiUrl = "http://localhost:8080/api/students";
const form = document.getElementById("studentForm");
const tableBody = document.querySelector("#studentTable tbody");

async function fetchStudents() {
    const res = await fetch(apiUrl);
    const students = await res.json();
    tableBody.innerHTML = "";
    students.forEach(student => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${student.name}</td>
            <td>${student.email}</td>
            <td>${student.course}</td>
            <td><button onclick="deleteStudent(${student.id})">Delete</button></td>
        `;
        tableBody.appendChild(row);
    });
}

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const student = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        course: document.getElementById("course").value
    };

    try {
        const response = await fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(student)
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Failed to add student:", errorText);
            alert("Error: " + errorText);
            return;
        }

        form.reset();
        fetchStudents();

    } catch (error) {
        console.error("Error submitting form:", error);
        alert("Network error. Make sure backend is running.");
    }
});

fetchStudents();

async function deleteStudent(id) {
    await fetch(`${apiUrl}/${id}`, { method: "DELETE" });
    fetchStudents();
}

fetchStudents();
