/* Action Dropdown JavaScript */
function toggleDropdown(event, button) {
    event.stopPropagation();
    
    const container = button.closest('.action-menu-container');
    const dropdown = container.querySelector('.dropdown-menu');
    
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== dropdown) {
            menu.style.display = 'none';
        }
    });
    
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.action-menu-container')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});
