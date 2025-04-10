function addToCart(event) {
    const id = event.target.getAttribute('data-id');
    const size = event.target.getAttribute('data-size');

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ id, size })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshCart();  
        } else {
            alert('Не удалось добавить товар в корзину.');
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

function removeFromCart(event) {
    const id = event.target.getAttribute('data-id');
    const size = event.target.getAttribute('data-size');

    fetch('/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ id, size })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshCart();
        } else {
            alert('Не удалось удалить товар из корзины.');
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

function refreshCart() {
    fetch('/cart', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest' 
        }
    })
    .then(response => response.text())
    .then(html => {
        const container = document.querySelector('.cart-container');
        if (container) {
            container.innerHTML = html;
        }
    })
    .catch(error => console.error('Ошибка обновления корзины:', error));
}

function checkPage(){
    const currPage = window.location.href;
    return currPage;
}

window.onunload = function() {
    fetch('/save-cart', {
        method: 'POST',
        body: JSON.stringify(getCartFromCookies()),
        headers: {
            'Content-Type': 'application/json'
        }
    });
};

function getCartFromCookies() {
    const cart = document.cookie
        .split('; ')
        .find(row => row.startsWith('cart='))
        ?.split('=')[1];
    return cart ? JSON.parse(decodeURIComponent(cart)) : {};
}

let canResend = true;
let resendTimeout;

document.getElementById('cart-container').addEventListener('click', function (event) {
    if (event.target && event.target.id === 'confirmButton') {
        const modal = new bootstrap.Modal(document.getElementById('deliveryModal'));
        modal.show();
    }
});

document.getElementById('sendCodeButton').addEventListener('click', async function() {
    const name = document.getElementById('nameInput').value.trim();
    const address = document.getElementById('addressInput').value.trim();

    if (!name || !address) {
        alert('Пожалуйста, заполните все поля');
        return;
    }

    try {
        const response = await fetch('/confirm-delivery', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name, address })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Ошибка при отправке кода');
        }

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        
        startResendTimer();
        
    } catch (error) {
        console.error('Ошибка:', error);
        alert(error.message);
    }
});

document.getElementById('confirmCodeButton').addEventListener('click', async function() {
    const code = document.getElementById('confirmationCodeInput').value.trim();

    if (!code || !/^\d{4}$/.test(code)) {
        alert('Пожалуйста, введите 4-значный код');
        return;
    }

    try {
        const response = await fetch('/verify-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ code })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Неверный код подтверждения');
        }

        alert('Код подтвержден успешно!');
        bootstrap.Modal.getInstance(document.getElementById('deliveryModal')).hide();

        
    } catch (error) {
        console.error('Ошибка:', error);
        alert(error.message);
    }
});

document.getElementById('resendCodeButton').addEventListener('click', function() {
    if (!canResend) return;
    
    document.getElementById('sendCodeButton').click();
});

document.getElementById('backButton').addEventListener('click', function() {
    document.getElementById('step1').style.display = 'block';
    document.getElementById('step2').style.display = 'none';
});

function startResendTimer() {
    canResend = false;
    document.getElementById('resendCodeButton').style.display = 'none';
    const timerElement = document.getElementById('resendTimer');
    timerElement.style.display = 'inline';
    
    let secondsLeft = 300; 
    
    const updateTimer = () => {
        const minutes = Math.floor(secondsLeft / 60);
        const seconds = secondsLeft % 60;
        timerElement.textContent = `Повторная отправка через ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        
        if (secondsLeft <= 0) {
            clearInterval(interval);
            canResend = true;
            timerElement.style.display = 'none';
            document.getElementById('resendCodeButton').style.display = 'inline';
        }
        secondsLeft--;
    };
    
    updateTimer();
    const interval = setInterval(updateTimer, 1000);
}