const cards = document.querySelectorAll('.member-card');

let current = 0;

function showSlide(index){

    cards.forEach(card => {
        card.classList.remove('active');
    });

    cards[index].classList.add('active');
}

function nextSlide(){

    current++;

    if(current >= cards.length){
        current = 0;
    }

    showSlide(current);
}

function prevSlide(){

    current--;

    if(current < 0){
        current = cards.length - 1;
    }

    showSlide(current);
}