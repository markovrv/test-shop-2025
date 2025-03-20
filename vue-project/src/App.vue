<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from 'vuestic-ui'

const goods = ref([])
const cart = ref([])

const user = ref({
  firstName: '',
  lastName: '',
  date: '',
  time: '',
  address: '',
  paymentMethod: ''
})

const doShowModal = ref(false)
const doShowCart = ref(false)

const { init } = useToast()

const countInCart = computed(() => (
  cart.value.reduce((total, item) => total + item.count, 0)
))

const sumInCart = computed(() => {
  return cart.value.reduce((total, item) => total + (item.count * item.price), 0);
})

const formData = ref({
  title: '',
  price: 0,
  description: '',
  image: ''
})

function getGoods() {
  fetch('https://shop.vyatgeo.ru/api/goods/index.php')
    .then(response => response.json())
    .then(data => {
      goods.value = data.map(el => ({
        id: parseInt(el.id),
        description: el.description,
        price: parseInt(el.price),
        title: el.title,
        image: el.image,
        count: 1
      }));
    })
    .catch(error => console.error('Error:', error));
}

function toCart(good) {
  var goodInCart = cart.value.find(el => (el.id == good.id))
  if (goodInCart) {
    goodInCart.count += good.count
  } else {
    cart.value.push({ id: good.id, title: good.title, count: good.count, price: good.price })
  }
  init({
    message: 'Товар добавлен в корзину!',
    offsetX: 100,
    offsetY: 100,
  });
}

function addGood() {
  fetch('https://shop.vyatgeo.ru/api/goods/index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(formData.value)
  })
    .then(response => response.json())
    .then(data => {
      getGoods();
      init('Товар добавлен в магазин!');
    })
    .catch(error => console.error('Error:', error));
}

function sendOrder() {

  user.value.goods = JSON.stringify(cart.value)

  fetch('https://shop.vyatgeo.ru/api/orders/index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(user.value)
  })
    .then(response => response.json())
    .then(data => {
      doShowCart.value = false
      cart.value = []
      init('Заказ отправлен!');
    })
    .catch(error => console.error('Error:', error));
}

function delGood(id) {
  fetch('https://shop.vyatgeo.ru/api/goods/index.php?id='+id, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    }
  })
    .then(response => response.json())
    .then(() => {
      init('Товар удален!');
      getGoods();
    })
    .catch(error => console.error('Error:', error));
}

onMounted(() => {
  getGoods();
})

</script>

<template>
  <VaNavbar color="warning" class="mb-3">
    <template #left>
      <VaNavbarItem class="logo">
        Магазин
      </VaNavbarItem>
    </template>
    <template #right>
      <VaNavbarItem class="hidden sm:block">
        <VaButtonGroup gradient color="#FFD166">
          <VaButton @click="$refs.modal.show()">
            Добавить товар
          </VaButton>
          <VaButton @click="$refs.modalCart.show()">
            Корзина ({{ countInCart }})
          </VaButton>
        </VaButtonGroup>
      </VaNavbarItem>
    </template>
  </VaNavbar>
  <div class="container">
    <div class="row">
      <div v-for="good in goods" class="col-md-4 col-sm-6">
        <VaCard style="margin-bottom: 14px">
          <VaImage :src="good.image" class="h-52" style="max-height: 300px;" />
          <VaCardTitle>
            {{ good.title }}
            <VaButton preset="plain" icon="delete" class="ml-3" @click="delGood(good.id)" />
          </VaCardTitle>
          <VaCardContent>
            {{ good.description }}
          </VaCardContent>
          <h1 class="price">{{ good.price }}&#8381;</h1>
          <div style="padding-bottom: 15px; display: flex;flex-direction: row;justify-content: space-evenly;">
            <VaCounter :min="1" v-model="good.count" />
            <VaButton @click="toCart(good)"> Купить </VaButton>
          </div>
        </VaCard>
      </div>
    </div>

  </div>

  <VaModal v-model="doShowCart" ref="modalCart" hide-default-actions overlay-opacity="0.2">
    <template #header>
      <h3 class="va-h3">Корзина</h3>
    </template>

    <VaDataTable noDataHtml="Корзина пуста" :items="cart"
      :columns="['id', 'title', 'count', 'price', 'sum', 'actions']">

      <template #cell(count)="{ value, row }">
        <VaCounter :min="1" v-model="row.itemKey.count" />
      </template>

      <template #cell(sum)="{ row }">
        {{ row.itemKey.count * row.itemKey.price }}
      </template>

      <template #cell(actions)="{ rowIndex }">
        <VaButton icon="clear" color="danger" @click="cart.splice(rowIndex, 1)">
          Удалить
        </VaButton>
      </template>

      <template #bodyAppend>
        В корзине {{ countInCart }} товаров на сумму {{ sumInCart }}.
      </template>

    </VaDataTable>


    <VaForm ref="formRef" class="flex flex-col items-baseline gap-6" style="padding: 16px;">
      <VaInput v-model="user.firstName"  style="margin: 16px;"
        label="Имя" />

      <VaInput v-model="user.lastName" style="margin: 16px;"
        label="Фамилия" />
      Адрес:
      <textarea style="margin: 16px; width: 100%;" v-model="user.address"></textarea>

      <VaDateInput v-model="user.date" label="Дата доставки" manual-input style="margin: 16px;"
        clearable />

      <VaTimeInput v-model="user.time" label="Время доставки" style="margin: 16px;"
        manual-input clearable />

      <div style="margin: 16px;">
        <span class="va-title">Способ оплаты</span>
        <VaOptionList v-model="user.paymentMethod" :options="['СБП', 'Карта', 'Наличные']"
          type="radio" />
      </div>

    </VaForm>



    <template #footer>
      <VaButton @click="sendOrder()">Отправить заказ</VaButton>
    </template>
  </VaModal>

  <VaModal ref="modal" v-model="doShowModal" @ok="addGood()">
    <h3 class="va-h3">
      Добавление товара
    </h3>
    <div class="form-grid">
      <div class="label">Название</div>
      <div>
        <VaInput v-model="formData.title" />
      </div>
      <div class="label">Цена</div>
      <div>
        <VaInput v-model="formData.price" />
      </div>
      <div class="label">Описание</div>
      <div>
        <VaInput v-model="formData.description" />
      </div>
      <div class="label">Изображение</div>
      <div>
        <VaInput v-model="formData.image" />
      </div>
    </div>
  </VaModal>


</template>

<style scoped>
.price {
  text-align: center;
  padding: 15px;
  font-size: 300%;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  /* Расстояние между ячейками */
  margin: 20px;
  /* Отступ вокруг формы */
  justify-items: end;
}

.form-grid .label {
  display: flex;
  align-items: center;
}
</style>
