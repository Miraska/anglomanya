// Slider
function inicializeSlider() {
    document.addEventListener("DOMContentLoaded", () => {
        const teachers = [
            {
                name: "Джон Смит",
                text: "— Опытный преподаватель с более чем 10-летним стажем. Специализируется на бизнес-английском и подготовке к международным экзаменам (IELTS, TOEFL). Его курсы отличаются практической направленностью и использованием современных методов обучения.",
                img: "assets/media/images/people/1.png"
            },
            {
                name: "Мария Иванова",
                text: "— Преподаватель с международным сертификатом CELTA. Работала с корпоративными клиентами и студентами из 20+ стран. Её занятия мотивируют и дают уверенность при общении на английском.",
                img: "assets/media/images/people/2.jpg"
            },
            {
                name: "Роберт Браун",
                text: "— Носитель языка, выпускник Лондонского университета. Специализируется на разговорном английском и улучшении произношения. Уроки проходят в живой и непринуждённой атмосфере.",
                img: "assets/media/images/people/3.jpg"
            }
        ];

        // Создаем элементы слайдера
        const sliderTrack = document.querySelector('.slider-track');
        const dotsContainer = document.querySelector('.slider-dots');
        
        // Заполняем слайдер
        teachers.forEach((teacher, index) => {
            // Создаем слайд
            const slide = document.createElement('div');
            slide.className = 'teacher-card';
            slide.innerHTML = `
                <div class="teacher-text">
                    <h3>${teacher.name}</h3>
                    <p>${teacher.text}</p>
                </div>
                <div class="teacher-photo">
                    <img src="${teacher.img}" alt="${teacher.name}">
                </div>
            `;
            sliderTrack.appendChild(slide);
            
            // Создаем точку для навигации
            const dot = document.createElement('span');
            dot.className = 'dot';
            if (index === 0) dot.classList.add('active');
            dot.dataset.index = index;
            dotsContainer.appendChild(dot);
        });

        const slides = document.querySelectorAll('.teacher-card');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        
        let currentIndex = 0;
        const slideCount = slides.length;

        // Функция для обновления позиции слайдера
        function updateSlider() {
            sliderTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Обновляем активную точку
            dots.forEach(dot => dot.classList.remove('active'));
            dots[currentIndex].classList.add('active');
        }

        // Обработчики для кнопок навигации
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + slideCount) % slideCount;
            updateSlider();
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % slideCount;
            updateSlider();
        });

        // Обработчики для точек
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                currentIndex = parseInt(dot.dataset.index);
                updateSlider();
            });
        });

        // Автопереключение слайдов
        let autoplayInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % slideCount;
            updateSlider();
        }, 6000);

        // Останавливаем автопереключение при наведении на слайдер
        const slider = document.querySelector('.teachers-slider');
        slider.addEventListener('mouseenter', () => {
            clearInterval(autoplayInterval);
        });
        
        slider.addEventListener('mouseleave', () => {
            autoplayInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % slideCount;
                updateSlider();
            }, 6000);
        });
    });
}

// Аккордеон для FAQ
function initializeAccordeon(){
    const accordions = document.querySelectorAll('.accordion');
    if (accordions.length > 0) {
    accordions.forEach(accordion => {
        accordion.addEventListener('click', function () {
        this.classList.toggle('active');
        const panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
        });
    });
    }
}


// Initialize common functionality
function initializeCommon() {
  if (typeof inicializeSlider === "function") inicializeSlider();
  if (typeof initializeAccordeon === "function") initializeAccordeon();
}


initializeCommon();
