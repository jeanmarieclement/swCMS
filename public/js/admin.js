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
});
