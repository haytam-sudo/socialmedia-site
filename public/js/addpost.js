const openAddPost = document.getElementById('openAddPost');
const modal = document.getElementById('addPostModal');
const closeAddPost = document.getElementById('closeAddPost');
const cancelBtn = document.getElementById('cancelAddPost');

function openModal() {
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('no-scroll');
}

function closeModal() {
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('no-scroll');
}

openAddPost.addEventListener('click', openModal);
closeAddPost.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);

// click outside closes
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

// ESC closes
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
});