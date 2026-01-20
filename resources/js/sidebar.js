const sidebar = document.querySelector('.sidebar');
const toggleSidebarItem = document.querySelector('.toggle-sidebar');
const sidebarItems = document.querySelectorAll('.sidebar-item');
const noTransitionClass = 'no-transition';

// Load sidebar state from localStorage
const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
isCollapsed ? CollapseSidebar() : UnfoldSidebar();

// Add the class to disable the animation
sidebar.classList.add(noTransitionClass);
setTimeout(() => sidebar.classList.remove(noTransitionClass), 0);

toggleSidebarItem.addEventListener('click', toggleSidebar);
toggleSidebarItem.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ' || event.keyCode === 32) {
        event.preventDefault();
        toggleSidebar();
    }
});

function toggleSidebar() {
    sidebar.classList.contains('collapsed') ? UnfoldSidebar() : CollapseSidebar();
    updateTabIndex();
}

function CollapseSidebar(temp = null) {
    if (!sidebar.classList.contains("collapsed")) {
        sidebar.classList.add("collapsed");
        if (temp !== true) localStorage.setItem('sidebarCollapsed', true);
        document.getElementById('Unfold').style.display = "block";
        document.getElementById('Collapse').style.display = "none";
    }
}

function UnfoldSidebar(temp = null) {
    if (sidebar.classList.contains("collapsed")) {
        sidebar.classList.remove('collapsed');
        if (temp !== true) localStorage.setItem('sidebarCollapsed', false);
        document.getElementById('Unfold').style.display = "none";
        document.getElementById('Collapse').style.display = "block";
    }
}

function handleResize() {
    const handleResizeWith = window.location.pathname.split("/")[2] === "requestlog.php" ? 875 : 700;
    if (window.innerWidth < handleResizeWith) {
        CollapseSidebar(true);
    } else if (!isCollapsed) {
        UnfoldSidebar();
    }
}

// Add event listener for window resize
window.addEventListener('resize', handleResize);
handleResize();

function updateTabIndex() {
    sidebarItems.forEach(item => {
        const img = item.querySelector('img');
        const hiddenspan = item.querySelector('span');
        img.tabIndex = (hiddenspan && (hiddenspan.style.display === 'none' || getComputedStyle(hiddenspan).display === 'none')) ? 0 : -1;

        // Add keydown event listener to the sidebar item
        item.addEventListener('keydown', (event) => {
            if (!item.classList.contains('toggle-sidebar') && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                window.location.href = item.dataset.content;
            }
        });
    });
}

function markactivesidebar() {
    const sidebar_items = document.getElementsByClassName('sidebar-item');
    const currentPath = window.location.pathname.split("/")[2];
    
    for (let i = 0; i < sidebar_items.length; i++) {
        const item = sidebar_items[i];
        const link = item.querySelector('a');

        if (link && link.href) {
            const linkPath = new URL(link.href).pathname.split("/")[2];
            
            if (linkPath === currentPath) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        }
    }
}

// sets tabindex at websiteload
updateTabIndex();
markactivesidebar();