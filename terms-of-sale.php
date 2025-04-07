<?php
// Начинаем сессию
session_start();

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
  <h1 class="text-3xl font-bold mb-6 text-white">Условия продажи</h1>
  
  <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
    <p class="mb-4 text-zinc-300">Последнее обновление: 1 апреля 2025 г.</p>
    
    <div class="space-y-6 text-zinc-300">
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">1. Общие положения</h2>
        <p>
          Настоящие Условия продажи регулируют отношения между продавцами, покупателями и платформой GameMarket 
          при совершении сделок купли-продажи игровых товаров и услуг. Используя нашу платформу для продажи или 
          покупки товаров, вы соглашаетесь с настоящими Условиями продажи.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">2. Регистрация продавца</h2>
        <p>
          Для продажи товаров на GameMarket необходимо зарегистрироваться в качестве продавца. При регистрации 
          вы обязуетесь предоставить точную, актуальную и полную информацию о себе. Вы несете ответственность 
          за поддержание конфиденциальности вашего аккаунта и пароля.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">3. Размещение товаров</h2>
        <p>При размещении товаров на продажу, продавцы обязуются:</p>
        <ul class="list-disc pl-6 mt-2 space-y-1">
          <li>Предоставлять точное и полное описание товара или услуги.</li>
          <li>Указывать честную и разумную цену.</li>
          <li>Размещать только те товары и услуги, которые разрешены правилами платформы.</li>
          <li>Не нарушать авторские права, товарные знаки или другие права интеллектуальной собственности.</li>
          <li>Соблюдать все применимые законы и правила.</li>
        </ul>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">4. Запрещенные товары и услуги</h2>
        <p>На платформе GameMarket запрещено продавать:</p>
        <ul class="list-disc pl-6 mt-2 space-y-1">
          <li>Товары или услуги, нарушающие условия использования игр.</li>
          <li>Взломанные или пиратские версии игр.</li>
          <li>Читы, боты или другие программы, нарушающие правила игр.</li>
          <li>Товары или услуги, полученные незаконным путем.</li>
          <li>Любые другие товары или услуги, запрещенные законодательством.</li>
        </ul>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">5. Процесс покупки и продажи</h2>
        <p>
          Процесс покупки и продажи на GameMarket включает следующие этапы:
        </p>
        <ol class="list-decimal pl-6 mt-2 space-y-1">
          <li>Покупатель выбирает товар и оформляет заказ.</li>
          <li>Покупатель оплачивает товар через платформу.</li>
          <li>Продавец получает уведомление о заказе и подтверждает его.</li>
          <li>Продавец передает товар или оказывает услугу покупателю.</li>
          <li>Покупатель подтверждает получение товара или услуги.</li>
          <li>Средства переводятся продавцу за вычетом комиссии платформы.</li>
        </ol>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">6. Оплата и комиссии</h2>
        <p>
          GameMarket взимает комиссию с каждой успешной сделки. Размер комиссии зависит от категории товара 
          и составляет от 5% до 15% от стоимости товара. Точный размер комиссии указывается при размещении товара.
        </p>
        <p class="mt-2">
          Оплата производится через безопасные платежные системы, интегрированные с нашей платформой. 
          Средства продавца хранятся на счете платформы до подтверждения получения товара покупателем.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">7. Разрешение споров</h2>
        <p>
          В случае возникновения спора между покупателем и продавцом, GameMarket предоставляет систему разрешения споров. 
          Обе стороны могут предоставить доказательства и объяснения, после чего модераторы платформы примут решение 
          на основе предоставленной информации и условий использования платформы.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">8. Возврат средств</h2>
        <p>
          Возврат средств возможен в следующих случаях:
        </p>
        <ul class="list-disc pl-6 mt-2 space-y-1">
          <li>Товар не соответствует описанию.</li>
          <li>Товар не был доставлен в течение оговоренного срока.</li>
          <li>Услуга не была оказана или была оказана некачественно.</li>
        </ul>
        <p class="mt-2">
          Запрос на возврат средств должен быть подан в течение 48 часов после получения товара или услуги. 
          Решение о возврате средств принимается модераторами платформы на основе предоставленных доказательств.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">9. Ответственность</h2>
        <p>
          Продавцы несут полную ответственность за товары и услуги, которые они предлагают на платформе. 
          GameMarket не несет ответственности за качество товаров и услуг, но стремится обеспечить безопасную 
          и честную среду для совершения сделок.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">10. Изменения в Условиях продажи</h2>
        <p>
          GameMarket оставляет за собой право изменять настоящие Условия продажи в любое время. 
          Изменения вступают в силу с момента их публикации на сайте. Продолжая использовать платформу 
          после внесения изменений, вы соглашаетесь с обновленными условиями.
        </p>
      </section>
      
      <section>
        <h2 class="text-xl font-bold mb-3 text-white">11. Контактная информация</h2>
        <p>
          Если у вас есть вопросы или предложения относительно наших Условий продажи, 
          пожалуйста, свяжитесь с нами по адресу: info@gamemarket.ru
        </p>
      </section>
    </div>
  </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

