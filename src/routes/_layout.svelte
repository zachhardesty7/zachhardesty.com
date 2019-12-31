<script>
  import { Nav } from "../components";

  export let segment;
</script>

<Nav {segment} />

<main>
  <slot />
</main>
