
//Sidebar wrapping up js
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.wrapper');
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('mainSidebarToggle');
    
    // Track karenge ki user ne button click karke sidebar chota (pin) kiya hai ya nahi
    let isPinned = false; 

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Agar Mobile Screen hai to sidebar right se aayega
            if (window.innerWidth < 992) {
                document.documentElement.classList.toggle('nav_open');
            } 
            // Desktop Screen Logic
            else {
                isPinned = !isPinned; // Toggle State
                
                if (isPinned) {
                    // Slim ho gaya
                    wrapper.classList.add('sidebar_minimize');
                    wrapper.classList.remove('sidebar_minimize_hover');
                } else {
                    // Wapas full fix ho gaya
                    wrapper.classList.remove('sidebar_minimize');
                    wrapper.classList.remove('sidebar_minimize_hover');
                }
            }
        });
    }

    // Slim Sidebar par Hover Effect track karne ke liye
    if (sidebar) {
        // Jab user mouse sidebar par laye
        sidebar.addEventListener('mouseenter', function() {
            if (isPinned && window.innerWidth >= 992) {
                // Temporarily expand karo bina main-panel ko push kiye
                wrapper.classList.remove('sidebar_minimize');
                wrapper.classList.add('sidebar_minimize_hover');
            }
        });

        // Jab user mouse sidebar se bahar nikal le
        sidebar.addEventListener('mouseleave', function() {
            if (isPinned && window.innerWidth >= 992) {
                // Wapas slim (collapse) kardo
                wrapper.classList.remove('sidebar_minimize_hover');
                wrapper.classList.add('sidebar_minimize');
            }
        });
    }
});