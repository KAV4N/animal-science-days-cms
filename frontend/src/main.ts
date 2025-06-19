import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import { primevueConfig } from './plugins/primevue'
import ConfirmationService from 'primevue/confirmationservice'
import ToastService from 'primevue/toastservice'

// Import PrimeVue components
import Accordion from 'primevue/accordion'
import AccordionTab from 'primevue/accordiontab'
import AutoComplete from 'primevue/autocomplete'
import Avatar from 'primevue/avatar'
import Badge from 'primevue/badge'
import Breadcrumb from 'primevue/breadcrumb'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Checkbox from 'primevue/checkbox'
import CheckboxGroup from 'primevue/checkboxgroup'
import Chip from 'primevue/chip'
import ColorPicker from 'primevue/colorpicker'
import Column from 'primevue/column'
import ConfirmDialog from 'primevue/confirmdialog'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import Divider from 'primevue/divider'
import Drawer from 'primevue/drawer'
import Dropdown from 'primevue/dropdown'
import FileUpload from 'primevue/fileupload'
import IconField from 'primevue/iconfield'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import InputIcon from 'primevue/inputicon'
import InputNumber from 'primevue/inputnumber'
import InputSwitch from 'primevue/inputswitch'
import InputText from 'primevue/inputtext'
import Menu from 'primevue/menu'
import Menubar from 'primevue/menubar'
import Message from 'primevue/message'
import MultiSelect from 'primevue/multiselect'
import PanelMenu from 'primevue/panelmenu'
import Password from 'primevue/password'
import ProgressBar from 'primevue/progressbar'
import ProgressSpinner from 'primevue/progressspinner'
import RadioButton from 'primevue/radiobutton'
import Rating from 'primevue/rating'
import SelectButton from 'primevue/selectbutton'
import Slider from 'primevue/slider'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabMenu from 'primevue/tabmenu'
import TabPanel from 'primevue/tabpanel'
import TabPanels from 'primevue/tabpanels'
import Tabs from 'primevue/tabs'
import TabView from 'primevue/tabview'
import Tag from 'primevue/tag'
import Textarea from 'primevue/textarea'
import Timeline from 'primevue/timeline'
import Toast from 'primevue/toast'
import ToggleButton from 'primevue/togglebutton'
import ToggleSwitch from 'primevue/toggleswitch'
import Toolbar from 'primevue/toolbar'

// Import directives
import Ripple from 'primevue/ripple'
import StyleClass from 'primevue/styleclass'
import Tooltip from 'primevue/tooltip'

import App from './App.vue'
import router from './router'

import { useAuthStore } from '@/stores/authStore'
import './plugins/axios'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'

import { tokenManager } from '@/utils/tokenManager';

startApp()

async function startApp() {
  const pinia = createPinia()
  const app = createApp(App)
  
  app.use(pinia)
    
  // Use PrimeVue plugins
  app.use(ToastService)
  app.use(ConfirmationService)
  app.use(PrimeVue, primevueConfig)
  
  const authStore = useAuthStore();


  if (!tokenManager.hasTokens()) {
    try {
      await authStore.checkAuth();
    } catch (error) {
      tokenManager.clearAllTokens();
    }
  }

  if (tokenManager.getAccessToken()) {
    await authStore.fetchCurrentUser();
  }
 

  // Register global directives
  app.directive('ripple', Ripple)
  app.directive('tooltip', Tooltip)
  app.directive('styleclass', StyleClass)


  app.component('dropdown', Dropdown) 

  // Register PrimeVue components
  app.component('Accordion', Accordion)
  app.component('AccordionTab', AccordionTab)
  app.component('AutoComplete', AutoComplete)
  app.component('Avatar', Avatar)
  app.component('Badge', Badge)
  app.component('Breadcrumb', Breadcrumb)
  app.component('Button', Button)
  app.component('DatePicker', DatePicker)
  app.component('Card', Card)
  app.component('Checkbox', Checkbox)
  app.component('CheckboxGroup', CheckboxGroup)
  app.component('Chip', Chip)
  app.component('ColorPicker', ColorPicker)
  app.component('Column', Column)
  app.component('ConfirmDialog', ConfirmDialog)
  app.component('DataTable', DataTable)
  app.component('Dialog', Dialog)
  app.component('Divider', Divider)
  app.component('Drawer', Drawer)
  app.component('Select', Select)
  app.component('FileUpload', FileUpload)
  app.component('IconField', IconField)
  app.component('InputGroup', InputGroup)
  app.component('InputGroupAddon', InputGroupAddon)
  app.component('InputIcon', InputIcon)
  app.component('InputNumber', InputNumber)
  app.component('InputSwitch', InputSwitch)
  app.component('InputText', InputText)
  app.component('Menu', Menu)
  app.component('Menubar', Menubar)
  app.component('Message', Message)
  app.component('MultiSelect', MultiSelect)
  app.component('PanelMenu', PanelMenu)
  app.component('Password', Password)
  app.component('ProgressBar', ProgressBar)
  app.component('ProgressSpinner', ProgressSpinner)
  app.component('RadioButton', RadioButton)
  app.component('Rating', Rating)
  app.component('SelectButton', SelectButton)
  app.component('Slider', Slider)
  app.component('Tab', Tab)
  app.component('TabList', TabList)
  app.component('TabMenu', TabMenu)
  app.component('TabPanel', TabPanel)
  app.component('TabPanels', TabPanels)
  app.component('Tabs', Tabs)
  app.component('TabView', TabView)
  app.component('Tag', Tag)
  app.component('Textarea', Textarea)
  app.component('Timeline', Timeline)
  app.component('Toast', Toast)
  app.component('ToggleButton', ToggleButton)
  app.component('ToggleSwitch', ToggleSwitch)
  app.component('Toolbar', Toolbar)
  
  app.use(router)
  app.mount('#app')
}