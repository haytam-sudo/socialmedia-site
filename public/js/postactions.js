/**
 * Post Actions Handler
 * Handles edit, delete, and menu functionality for posts
 */

document.addEventListener('DOMContentLoaded', function () {
    // Edit Post Modal
    const editModal = document.getElementById('editPostModal');
    const deleteModal = document.getElementById('deletePostModal');

    // Post Menu Buttons
    const postMenuBtns = document.querySelectorAll('.post-menu-btn');
    const editPostBtns = document.querySelectorAll('.edit-post-btn');
    const deletePostBtns = document.querySelectorAll('.delete-post-btn');

    // Edit Modal Buttons
    const closeEditBtns = document.querySelectorAll('.close-edit-modal');

    // Delete Modal Buttons
    const closeDeleteBtns = document.querySelectorAll('.close-delete-modal');

    /**
     * Toggle Post Menu Dropdown
     */
    postMenuBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = this.closest('.post-menu').querySelector('.post-menu-dropdown');
            const isHidden = dropdown.getAttribute('aria-hidden') === 'true';

            // Close all other dropdowns
            document.querySelectorAll('.post-menu-dropdown').forEach(dd => {
                dd.setAttribute('aria-hidden', 'true');
            });

            // Toggle current dropdown
            dropdown.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
        });
    });

    /**
     * Open Edit Modal
     */
    editPostBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');

            // Close dropdown
            this.closest('.post-menu-dropdown').setAttribute('aria-hidden', 'true');

            // Fetch post data
            try {
                // We'll populate with form data - in a real app you'd fetch the post data
                document.getElementById('editPostId').value = postId;
                document.getElementById('editContent').value = '';

                editModal.classList.add('show');
                editModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('no-scroll');
            } catch (error) {
                console.error('Error opening edit modal:', error);
            }
        });
    });

    /**
     * Open Delete Confirmation Modal
     */
    deletePostBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');

            // Close dropdown
            this.closest('.post-menu-dropdown').setAttribute('aria-hidden', 'true');

            document.getElementById('deletePostId').value = postId;
            deleteModal.classList.add('show');
            deleteModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('no-scroll');
        });
    });

    /**
     * Close Edit Modal
     */
    closeEditBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            editModal.classList.remove('show');
            editModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        });
    });

    /**
     * Close Delete Modal
     */
    closeDeleteBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            deleteModal.classList.remove('show');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        });
    });

    /**
     * Close modals on outside click
     */
    editModal.addEventListener('click', function (e) {
        if (e.target === editModal) {
            editModal.classList.remove('show');
            editModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        }
    });

    deleteModal.addEventListener('click', function (e) {
        if (e.target === deleteModal) {
            deleteModal.classList.remove('show');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        }
    });

    /**
     * Close modals on ESC key
     */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (editModal.classList.contains('show')) {
                editModal.classList.remove('show');
                editModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            }
            if (deleteModal.classList.contains('show')) {
                deleteModal.classList.remove('show');
                deleteModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            }
        }
    });

    /**
     * Close post menu dropdown when clicking outside
     */
    document.addEventListener('click', function () {
        document.querySelectorAll('.post-menu-dropdown').forEach(dd => {
            dd.setAttribute('aria-hidden', 'true');
        });
    });
});
