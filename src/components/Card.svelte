<script>
  export let title = "";
  export let description = "";
  export let link = "";
  export let github = "";
  export let skills = [];
  export let image;
</script>

<style>
  .card {
    display: flex;
    flex-direction: column;
    flex: 1;
    margin: 0.5rem 0 1rem 0;
    border-radius: 2px;
    transition: box-shadow 0.25s;
    box-shadow: 0 5px 10px rgba(154, 160, 185, 0.05),
      0 15px 40px rgba(166, 173, 201, 0.2);
    background-color: #fff;
  }

  .thumbnail {
    width: 100%;
    max-height: 200px;
    overflow: hidden;
  }

  .thumbnail img {
    width: 100%;
    opacity: 1;
    transition: opacity 0.3s ease;
  }

  .thumbnail img:hover {
    opacity: 0.75;
  }

  .card-stacked {
    position: relative;
  }

  .content {
    padding: 24px;
    border-radius: 0 0 2px 2px;
    flex-grow: 1;
    text-align: left;
  }

  .title-link {
    color: var(--primary);
  }

  .title-link:hover {
    opacity: 0.75;
  }

  .skill {
    display: inline-block;
    padding: 5px 10px;
    margin: 5px 10px 5px 0;
    background-color: var(--accent);
    color: #fff;
    border-radius: 4px;
  }

  .actions {
    position: relative;
    background-color: inherit;
    border-top: 1px solid rgba(160, 160, 160, 0.2);
    padding: 16px 24px;
  }

  .actions a {
    color: var(--primary);
    margin: 0 12px;
    text-transform: uppercase;
  }

  .actions a:hover {
    opacity: 0.75;
  }
</style>

<div class="card">
  {#if image}
    {#if link}
      <a class="link" href={link} target="blank_">
        <div class="thumbnail link">
          <img src="/{image}" alt={image} />
        </div>
      </a>
    {:else}
      <div class="thumbnail">
        <img src="/{image}" alt={image} />
      </div>
    {/if}
  {/if}

  <div class="card-stacked">
    <div class="content">
      <h4 class="title">
        {#if link || github}
          <a class="title-link" href={link || github} target="blank_">
            {title}
          </a>
        {:else}{title}{/if}
      </h4>
      <p>{description}</p>
      <div class="skills">
        <ul>
          {#each skills as skill}
            <li class="skill" key={skill}>{skill}</li>
          {/each}
        </ul>
      </div>
    </div>
    <div class="actions">
      {#if link}
        <a href={link} target="blank_">View Project</a>
      {/if}
      {#if github}
        <a href={github} target="blank_">View Source Code</a>
      {/if}
    </div>
  </div>
</div>
