<script>
  export let title = "";
  export let description = "";
  export let link = "";
  export let github = "";
  export let skills = [];
  export let image;
</script>

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

<style>
  .card {
    display: flex;
    flex-direction: column;
    flex: 1;
    margin: 0.5rem 0 1rem 0;
    border-radius: 2px;
    transition: box-shadow 0.25s;
    box-shadow:
      0 5px 10px rgba(154, 160, 185, 0.05),
      0 15px 40px rgba(166, 173, 201, 0.2);
    background-color: var(--grey-0);
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

  .title {
    color: var(--primary);
    font-size: 1.78rem;
    line-height: 110%;
    margin: 1.01rem 0 0.811rem 0;
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
    color: var(--grey-0);
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
    text-transform: uppercase;
    font-weight: 500;
    font-size: 0.8525rem;
    line-height: 1.75;
    letter-spacing: 0.02857em;
    padding: 10px 8px;
    border-radius: 4px;
    -webkit-transition:
      background-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      border-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
    transition:
      background-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      border-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms,
      color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
  }

  .actions a:hover {
    text-decoration: none;
    background-color: rgba(25, 118, 210, 0.04);
  }

  @media (prefers-color-scheme: dark) {
    .card {
      background-color: var(--grey-4);
      color: var(--text-2);
      /* not sure what this does */
      transition: box-shadow 300ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
      box-shadow:
        0px 2px 1px -1px rgb(0 0 0 / 20%),
        0px 1px 1px 0px rgb(0 0 0 / 14%),
        0px 1px 3px 0px rgb(0 0 0 / 12%);
      background-image: linear-gradient(
        rgba(255, 255, 255, 0.05),
        rgba(255, 255, 255, 0.05)
      );
      /* doesn't this do the same as above bg color/image? */
      /* background-color: hsl(0deg, 0%, 12.06%); */
    }

    .title,
    .title-link,
    .actions a {
      color: var(--grey-0);
    }

    .skill {
      color: var(--grey-4);
    }

    .actions {
      border-top: 1px solid rgba(80, 80, 80, 0.6);
    }

    .actions a:hover {
      text-decoration: none;
      background-color: rgba(144, 202, 249, 0.08);
    }
  }
</style>
