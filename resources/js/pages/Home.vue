<script lang="ts">
import NavigationBar from "@/layouts/app/NavigationBar.vue";
export default {
  components: {
    NavigationBar
  }
}
</script>

<template>
  <NavigationBar />
  <div class="content-container">
    <section class="hero-section">
      <h1>Welcome to our CMS Project</h1>
      <p>Scroll down to explore more content and see the navigation animation</p>
    </section>
    
    <section class="section">
      <h2>Section 1</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eget felis eget urna ultricies ultricies. Vivamus commodo, sem ut tempor interdum, nisi lorem euismod nisi, eget aliquet urna nulla eget lorem.</p>
      <p>Phasellus auctor, magna eget aliquam elementum, orci nisl sodales velit, sit amet feugiat ante elit id metus. Cras non enim vel augue rutrum sodales.</p>
    </section>
    
    <section class="section">
      <h2>Section 2</h2>
      <p>Fusce vehicula dolor arcu, sit amet blandit dolor mollis nec. Donec viverra eleifend lacus, vitae ullamcorper metus. Sed sollicitudin ipsum quis nunc sollicitudin ultrices.</p>
      <p>Maecenas non scelerisque purus, congue cursus arcu. Donec vel dapibus mi. Mauris maximus posuere placerat. Sed et libero eu nibh tristique mollis a eget lectus.</p>
    </section>
    
    <section class="section">
      <h2>Section 3</h2>
      <p>Aliquam erat volutpat. Vestibulum dapibus hendrerit nibh ut ornare. Cras luctus venenatis lacus, a tincidunt urna sagittis et. Ut ac augue libero.</p>
      <p>Pellentesque sit amet sapien fringilla, mattis ligula consectetur, ultrices mauris. Maecenas vitae mattis tellus. Nullam quis imperdiet augue.</p>
    </section>
    
    <section class="section">
      <h2>Section 4</h2>
      <p>Vestibulum ullamcorper risus auctor eleifend consequat. Vivamus mollis in tellus ac ullamcorper. Vestibulum sit amet bibendum ipsum, vitae rutrum ex.</p>
      <p>Donec lacinia venenatis metus at bibendum? In hac habitasse platea dictumst. Proin ac nibh rutrum lectus rhoncus eleifend.</p>
    </section>
  </div>
</template>

<style scoped>

.hero-section {
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  background-color: #3f4e61;
  padding: 0 20px;
}

.hero-section h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.section {
  min-height: 100vh;
  padding: 4rem 2rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.section:nth-child(odd) {
  background-color: #3f4e61;
}

.section h2 {
  font-size: 2.5rem;
  margin-bottom: 2rem;
}

.section p {
  font-size: 1.2rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  max-width: 800px;
}

@media (max-width: 768px) {
  .hero-section h1 {
    font-size: 2rem;
  }
  
  .section h2 {
    font-size: 1.8rem;
  }
  
  .section p {
    font-size: 1rem;
  }
}
</style>
