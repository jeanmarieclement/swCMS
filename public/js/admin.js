/**
 * Admin JavaScript
 * Scripts for the admin interface
 */

document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Confirm before deleting items
  const deleteButtons = document.querySelectorAll(".btn-delete");
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function (e) {
      if (
        !confirm(
          "Are you sure you want to delete this item? This action cannot be undone."
        )
      ) {
        e.preventDefault();
      }
    });
  });

  // Toggle sidebar on mobile
  const sidebarToggle = document.querySelector(".sidebar-toggle");
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
      document.querySelector(".sidebar").classList.toggle("show");
    });
  }

  // Edit content
  // Publish button
  const publishBtn = document.getElementById("publishBtn");
  if (publishBtn) {
    publishBtn.addEventListener("click", function () {
      document.getElementById("contentStatus").value = "published";
      document.getElementById("contentForm").submit();
    });
  }

  // Save as Draft button
  const saveAsDraftBtn = document.getElementById("saveAsDraftBtn");
  if (saveAsDraftBtn) {
    saveAsDraftBtn.addEventListener("click", function () {
      document.getElementById("contentStatus").value = "draft";
      document.getElementById("contentForm").submit();
    });
  }

  // Schedule button
  const scheduleBtn = document.getElementById("scheduleBtn");
  if (scheduleBtn) {
    scheduleBtn.addEventListener("click", function () {
      var modal = new bootstrap.Modal(document.getElementById("scheduleModal"));
      modal.show();
    });
  }

  // Confirm Schedule button
  const confirmScheduleBtn = document.getElementById("confirmScheduleBtn");
  if (confirmScheduleBtn) {
    confirmScheduleBtn.addEventListener("click", function () {
      var scheduleDatetime = document.getElementById("scheduleDatetime").value;
      document.getElementById("publishDate").value = scheduleDatetime;
      document.getElementById("contentStatus").value = "scheduled";

      var modal = bootstrap.Modal.getInstance(
        document.getElementById("scheduleModal")
      );
      modal.hide();
    });
  }

  // Select Image button
  const selectImageBtn = document.getElementById("selectImageBtn");
  if (selectImageBtn) {
    selectImageBtn.addEventListener("click", function () {
      var modal = new bootstrap.Modal(
        document.getElementById("mediaLibraryModal")
      );
      modal.show();
    });
  }

  // Auto-generate slug from title
  const titleInput = document.getElementById("title");
  if (titleInput) {
    titleInput.addEventListener("blur", function () {
      var slugField = document.getElementById("slug");
      if (slugField && slugField.value === "") {
        var titleValue = this.value;
        var slug = titleValue
          .toLowerCase()
          .replace(/[^\w\s-]/g, "") // Remove special chars
          .replace(/\s+/g, "-") // Replace spaces with -
          .replace(/--+/g, "-") // Replace multiple - with single -
          .trim(); // Trim leading/trailing spaces
        slugField.value = slug;
      }
    });
  }

  // Page Management Specific Functions
  
  // Auto-generate slug from title for page and article forms
  const titleField = document.getElementById('title');
  const slugField = document.getElementById('slug');
  
  if (titleField && slugField) {
    titleField.addEventListener('blur', function() {
      if (!slugField.value) {
        const title = titleField.value;
        if (title) {
          // Convert to lowercase, replace spaces and special chars with hyphens
          const slug = title.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special chars
            .replace(/\s+/g, '-')     // Replace spaces with hyphens
            .replace(/-+/g, '-')      // Replace multiple hyphens with single hyphen
            .trim();
          slugField.value = slug;
        }
      }
    });
  }

  // Status filter tabs
  const statusTabs = document.querySelectorAll('.nav-tabs .nav-link');
  if (statusTabs.length > 0) {
    statusTabs.forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.getAttribute('href');
      });
    });
  }

  // Preview button functionality
  const previewButtons = document.querySelectorAll('.btn-preview');
  if (previewButtons.length > 0) {
    previewButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        // If we're in a form, make sure content is updated from TinyMCE
        if (tinymce && tinymce.get('content')) {
          tinymce.get('content').save();
        }
        
        // Open preview in new tab
        window.open(this.getAttribute('href'), '_blank');
      });
    });
  }

  // Page order sorting
  const orderInputs = document.querySelectorAll('input[name="order"]');
  if (orderInputs.length > 0) {
    orderInputs.forEach(input => {
      input.addEventListener('change', function() {
        // Ensure value is a non-negative integer
        let value = parseInt(this.value);
        if (isNaN(value) || value < 0) {
          this.value = 0;
        } else {
          this.value = value;
        }
      });
    });
  }

  // Admin Sidebar Menu: active link, collapsible groups, and accessibility
  const sidebarMenu = document.getElementById('sidebarMenu');
  if (sidebarMenu) {
    const currentPath = window.location.pathname;
    const navLinks = sidebarMenu.querySelectorAll('.nav-link');
    let bestMatch = null;
    let bestMatchLength = 0;

    navLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (!href || href === '#' || href.startsWith('javascript:')) {
        return;
      }
      let linkPath = href;
      try {
        const parsed = new URL(href, window.location.origin);
        linkPath = parsed.pathname;
      } catch (e) {
        // fallback to href
      }

      if (linkPath === currentPath) {
        bestMatch = link;
        bestMatchLength = 9999;
      } else if (bestMatchLength < 9999 && currentPath.startsWith(linkPath) && linkPath !== '/admin' && linkPath !== '/admin/') {
        if (linkPath.length > bestMatchLength) {
          bestMatch = link;
          bestMatchLength = linkPath.length;
        }
      }
    });

    // Default to /admin/dashboard if on /admin or /admin/
    if (!bestMatch && (currentPath === '/admin' || currentPath === '/admin/')) {
      bestMatch = sidebarMenu.querySelector('a[href$="/admin/dashboard"]');
    }

    let activeGroupId = null;
    if (bestMatch) {
      bestMatch.classList.add('active');
      bestMatch.setAttribute('aria-current', 'page');

      // Find parent group and ensure it's open
      const parentCollapse = bestMatch.closest('.sidebar-group-collapse');
      if (parentCollapse) {
        activeGroupId = parentCollapse.getAttribute('id');
        parentCollapse.classList.add('show');
        const triggerBtn = document.querySelector(`[data-bs-target="#${activeGroupId}"]`);
        if (triggerBtn) {
          triggerBtn.classList.remove('collapsed');
          triggerBtn.setAttribute('aria-expanded', 'true');
        }
      }

      // Ensure active item is visible in the scrollable sidebar container
      setTimeout(() => {
        bestMatch.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }, 100);
    }

    // Apply saved collapse states from localStorage
    const storageKey = 'swcms_admin_sidebar_collapsed';
    let collapsedGroups = [];
    try {
      const saved = localStorage.getItem(storageKey);
      if (saved) {
        collapsedGroups = JSON.parse(saved);
      }
    } catch (e) {
      collapsedGroups = [];
    }

    if (Array.isArray(collapsedGroups)) {
      collapsedGroups.forEach(id => {
        // Never collapse the group containing the currently active page!
        if (id !== activeGroupId) {
          const collapseEl = document.getElementById(id);
          const triggerBtn = document.querySelector(`[data-bs-target="#${id}"]`);
          if (collapseEl && triggerBtn) {
            collapseEl.classList.remove('show');
            triggerBtn.classList.add('collapsed');
            triggerBtn.setAttribute('aria-expanded', 'false');
          }
        }
      });
    }

    // Listen to collapse events to persist user preferences
    const groupCollapses = sidebarMenu.querySelectorAll('.sidebar-group-collapse');
    groupCollapses.forEach(collapseEl => {
      collapseEl.addEventListener('hidden.bs.collapse', function () {
        const id = this.getAttribute('id');
        let list = [];
        try {
          list = JSON.parse(localStorage.getItem(storageKey)) || [];
        } catch (e) {}
        if (!list.includes(id)) {
          list.push(id);
        }
        try {
          localStorage.setItem(storageKey, JSON.stringify(list));
        } catch (e) {}
      });

      collapseEl.addEventListener('shown.bs.collapse', function () {
        const id = this.getAttribute('id');
        let list = [];
        try {
          list = JSON.parse(localStorage.getItem(storageKey)) || [];
        } catch (e) {}
        list = list.filter(item => item !== id);
        try {
          localStorage.setItem(storageKey, JSON.stringify(list));
        } catch (e) {}
      });
    });
  }
});
