<script>
  import { faChevronDown } from "@fortawesome/free-solid-svg-icons";
  import { Icons, Icon, Card } from "../components";
  import { experiences } from "../data";
</script>

<style>
  ul {
    width: fit-content;
    margin: auto;
  }
  li::marker {
    color: var(--primary);
    background-color: var(--primary);
  }
</style>

<div>
  <h3>Experience</h3>
  {#each experiences as job (job.employer)}
    <Card
      description={job.description}
      link=""
      github=""
      skills="[]"
      image
      title=""
    >
      <div class="card-stacked">
        <div class="content">
          <h5 class="position">{`${job.position}`}</h5>
          <h5 class="position">{`${job.employer} (${job.location})`}</h5>
          <p class="date">{job.date}</p>
          <ul>
            {#each job.bullets as bullet (bullet)}
              <li>{bullet}</li>
            {/each}
          </ul>
        </div>
      </div>
    </Card>
  {/each}
</div>
