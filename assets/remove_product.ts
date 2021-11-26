import Swal from 'sweetalert2';
let delButton = document.getElementById('delete-button');
let id = document.getElementById('current-entity-id').innerText;

delButton.addEventListener('click', ()=>{
    Swal.fire({
    title: 'Do you really want to delete this product?',
    showDenyButton: true,
    confirmButtonText: 'Delete',
    denyButtonText: `Cancel`,
  }).then(okay => {
    if(okay.isConfirmed) {
        window.location.href = `/products/remove/${id}`;
    }
  });
});