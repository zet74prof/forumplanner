console.log("Asset Mapper c\'est trop bien même si Noah il aime pas");

//search user from input (id=search) and filter rows from table body (id=userTable)
document.getElementById('search').addEventListener('keyup', function(e) {
    const search = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#userTable tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().indexOf(search) !== -1 ? '' : 'none';
    });
});