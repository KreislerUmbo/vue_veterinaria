<script setup>
import navItems from '@/navigation/vertical'
import { useConfigStore } from '@core/stores/config'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'
import { onMounted } from 'vue'

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })

// !SECTION
const configStore = useConfigStore()

// ℹ️ Provide animation name for vertical nav collapse icon.
const verticalNavHeaderActionAnimationName = ref(null)

watch([
  () => configStore.isVerticalNavCollapsed,
  () => configStore.isAppRTL,
], val => {
  if (configStore.isAppRTL)
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-back-180' : 'rotate-180'
  else
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-180' : 'rotate-back-180'
}, { immediate: true })

const navItemsV = ref([]);

onMounted(() => {
  let USER = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;
  if (USER) {
    console.log(USER);

    let permissions = USER.permissions; ///obtenemo la list e permisos el usuario autenticado

    navItems.forEach((nav) => {
      if (USER.role.name == 'Super-Admin') { //validamos si el ususario es super usuario
        navItemsV.value.push(nav);
      }
      else {
        if (nav.permission == 'all') {///los nav que tenga el permiso all pueeser visto por cualquier usuario
          navItemsV.value.push(nav);
        }

        if (nav.heading) {
          //filtramos los permisos que se necesita para ver los heading
          let headingP = nav.permissions.filter((permission) => {
            if (permissions.includes(permission)) {
              return true;
            }
            return false;
          });
          //si tenemos al menos un permiso puede verse el heading
          if (headingP.length > 0) {
            navItemsV.value.push(nav);
          }
        }

        if (nav.children) {//si el nav tiene sub menus
          let navT = nav;
          //filtramos si los submenus pueden ser vistos con los permisos del usuario autenticado
          let newChildren = nav.children.filter((subnav) => {
            if (permissions.includes(subnav.permission)) {
              return true;
            }
            return false;
          });
          //asignacion de los nuevos submenus
          navT.children = newChildren;
          navItemsV.value.push(navT);
        } else {
          //veririfamos si los permisos de usuario puden ver la opcion de navegacion
          if (permissions.includes(nav.permission)) {
            navItemsV.value.push(nav);
          }
        }

      }

    });
  }
})
</script>

<template>
  <VerticalNavLayout :nav-items="navItemsV">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn id="vertical-nav-toggle-btn" class="ms-n2 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon icon="ri-menu-line" />
        </IconBtn>

        <NavbarThemeSwitcher />

        <VSpacer />

        <NavBarI18n v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig" />
        <UserProfile />
      </div>
    </template>

    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Suspense :timeout="0" @fallback="isFallbackStateActive = true" @resolve="isFallbackStateActive = false">
        <Component :is="Component" />
      </Suspense>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>

<style lang="scss">
@keyframes rotate-180 {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(180deg);
  }
}

@keyframes rotate-back-180 {
  from {
    transform: rotate(180deg);
  }

  to {
    transform: rotate(0deg);
  }
}

.layout-vertical-nav {
  .nav-header {
    .header-action {
      animation-duration: 0.35s;
      animation-fill-mode: forwards;
      animation-name: v-bind(verticalNavHeaderActionAnimationName);
      transform: rotate(0deg);
    }
  }
}
</style>
