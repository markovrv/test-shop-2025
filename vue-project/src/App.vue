<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from 'vuestic-ui'

const goods = ref([])
const cart = ref([])
const doShowModal = ref(false)
const doShowCart = ref(false)

const { init } = useToast()

const countInCart = computed(() => (
  cart.value.reduce((total, item) => total + item.count, 0)
))

const formData = ref({
  title: '',
  price: 0,
  description: '',
  image: ''
})

function getGoods() {
  fetch('https://cc-kirov.site/api.php?type=goods')
    .then(response => response.json())
    .then(data => {
      goods.value = data.map(el => ({
        id: parseInt(el.id),
        description: el.value.description,
        price: parseInt(el.value.price),
        title: el.value.title,
        image: el.value.image,
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
  init('Товар добавлен в корзину!');
}

function addGood() {
  fetch('https://cc-kirov.site/api.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      type: 'goods',
      value: formData.value
    })
  })
    .then(response => response.json())
    .then(data => {
      getGoods();
      init('Товар добавлен в магазин!');
    })
    .catch(error => console.error('Error:', error));
}

function delGood(id) {
  fetch('https://cc-kirov.site/api.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id
    })
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
    <VaDataTable :items="cart.map(el => ({ ...el, sum: el.count * el.price }))"
      :columns="['id', 'title', 'count', 'price', 'sum', 'actions']">
      <template #cell(actions)="{ rowIndex }">
        <VaButton preset="plain" icon="delete" class="ml-3" @click="cart.splice(rowIndex, 1)" />
      </template>
      <template #cell(count)="{ rowIndex }">
        <VaCounter :min="1" v-model="cart[rowIndex].count" />
      </template>
    </VaDataTable>
    <template #footer>
      <VaButton @click="doShowCart = false">Отправить заказ</VaButton>
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
