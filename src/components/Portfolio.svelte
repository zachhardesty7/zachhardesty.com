<script>
  import { Card } from "../components";

  export let items = [];
</script>

<div>
  {#each items as item (item.title)}
    <Card
      title={item.title}
      description={item.description}
      link={item.link}
      github={item.github}
      skills={item.skills}
      image={item.image}
    />
  {/each}
</div>
