<section class="error-section">
    <div class="container">
        <div class="error-content">
            <!-- Декоративные элементы -->
            <div class="error-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
            </div>
            
            <!-- Основной контент -->
            <div class="error-main">
                <div class="error-number">
                    <span class="number-digit">4</span>
                    <div class="number-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="number-digit">4</span>
                </div>
                
                <div class="error-text">
                    <h1 class="display-md primary-500">Страница не найдена</h1>
                    <p class="body-lg gray-600" style="margin: 16px 0 32px; max-width: 500px;">
                        К сожалению, страница, которую вы ищете, не существует. 
                        Возможно, она была перемещена или удалена.
                    </p>
                    
                    <div class="error-actions">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i>
                            На главную
                        </a>
                        <a href="index.php?page=catalog" class="btn btn-ghost">
                            <i class="fas fa-book-open"></i>
                            В каталог курсов
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Дополнительная информация -->
            <div class="error-help">
                <div class="help-grid">
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="heading-sm">Проверьте адрес</h3>
                        <p class="body-sm gray-600">Убедитесь, что URL введен правильно</p>
                    </div>
                    
                    <div class="help-card">
                        <div class="help-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="heading-sm">Напишите нам</h3>
                        <p class="body-sm gray-600">Если проблема повторяется</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Стили для страницы 404 */
.error-section {
    position: relative;
    padding: 120px 0 80px;
    background: var(--gray-50);
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.error-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

/* Декоративные элементы */
.error-decoration {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: var(--primary-50);
    animation: float 6s ease-in-out infinite;
}

.circle-1 {
    width: 120px;
    height: 120px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.circle-2 {
    width: 80px;
    height: 80px;
    top: 60%;
    right: 15%;
    background: var(--primary-100);
    animation-delay: 2s;
}

.circle-3 {
    width: 60px;
    height: 60px;
    bottom: 20%;
    left: 15%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) scale(1);
        opacity: 0.8;
    }
    50% {
        transform: translateY(-20px) scale(1.05);
        opacity: 0.3;
    }
}

/* Основной контент */
.error-main {
    margin-bottom: 80px;
}

.error-number {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 40px;
}

.number-digit {
    font-size: 120px;
    font-weight: 800;
    color: var(--primary-500);
    line-height: 1;
    text-shadow: var(--shadow-lg);
}

.number-icon {
    width: 100px;
    height: 100px;
    background: var(--gradient-primary);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 40px;
    box-shadow: var(--shadow-xl);
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.error-text {
    margin-bottom: 40px;
}

.error-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.error-actions .btn {
    min-width: 180px;
}

/* Блок помощи */
.error-help {
    border-top: 1px solid var(--gray-200);
    padding-top: 60px;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(360px, 1fr));
    justify-content: center;
    gap: 32px;
    max-width: 900px;
    margin: 0 auto;
}

.help-card {
    background: white;
    padding: 32px 24px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 1px solid var(--gray-100);
}

.help-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.help-icon {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin: 0 auto 20px;
}

.help-card h3 {
    margin-bottom: 12px;
    color: var(--gray-800);
}

.help-card p {
    color: var(--gray-600);
}

/* Адаптивность */
@media (max-width: 768px) {
    .error-section {
        padding: 80px 0 40px;
    }
    
    .error-number {
        gap: 15px;
    }
    
    .number-digit {
        font-size: 80px;
    }
    
    .number-icon {
        width: 70px;
        height: 70px;
        font-size: 28px;
    }
    
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .error-actions .btn {
        width: 100%;
        max-width: 280px;
    }
    
    .help-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .decoration-circle {
        display: none;
    }
}

@media (max-width: 480px) {
    .error-number {
        gap: 10px;
    }
    
    .number-digit {
        font-size: 60px;
    }
    
    .number-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .error-text h1 {
        font-size: 32px;
    }
}
</style>