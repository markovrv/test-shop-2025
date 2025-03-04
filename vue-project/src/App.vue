<script setup>
import { ref } from 'vue'

const goods = ref([])
const cart = ref([])

function getGoods() {
  fetch('https://fakestoreapi.com/products').then(data => data.json())
    .then(elems => {
      for (var i = 0; i < elems.length; i++) {
        elems[i].count = 1;
      }
      goods.value = elems;
    })
}

function toCart(good) {
  var goodInCart = cart.value.find(el => (el.id == good.id))
  if (goodInCart) {
    goodInCart.count += good.count
  } else {
    cart.value.push({ id: good.id, title: good.title, count: good.count, price: good.price })
  }
}

</script>

<template>
  <VaNavbar color="warning" class="mb-3">
    <template #left>
      <VaNavbarItem class="logo">
        LOGO
      </VaNavbarItem>
    </template>
    <template #right>
      <VaNavbarItem class="hidden sm:block">
        Dashboard
      </VaNavbarItem>
      <VaNavbarItem class="hidden sm:block">
        Reports
      </VaNavbarItem>
      <VaNavbarItem class="hidden sm:block">
        Users
      </VaNavbarItem>
      <VaNavbarItem>
        <VaIcon name="mdi-magnify" />
      </VaNavbarItem>
      <VaNavbarItem>
        <VaIcon name="mdi-account-circle-outline" />
      </VaNavbarItem>
    </template>
  </VaNavbar>
  <div class="container">
    <VaButton @click="getGoods"> Получить товары </VaButton>{{ cart }}
    <div class="row">
      <div v-for="good in goods" class="col-md-4 col-sm-6">
        <VaCard style="margin-bottom: 14px">
          <VaImage :src="good.image" class="h-52" style="max-height: 300px;" />
          <VaCardTitle>{{ good.title }}</VaCardTitle>
          <VaCardContent>
            {{ good.description }}
          </VaCardContent>
          <h1 class="price">{{ Math.round(good.price * 92.85) }}&#8381;</h1>
          <div style="padding-bottom: 15px; display: flex;flex-direction: row;justify-content: space-evenly;">
            <VaCounter :min="1" v-model="good.count" />
            <VaButton @click="toCart(good)"> Купить </VaButton>
          </div>
        </VaCard>
      </div>
    </div>

  </div>

</template>

<style scoped>
.price {
  text-align: center;
  padding: 15px;
  font-size: 300%;
}
</style>
