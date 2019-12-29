import {
  faBriefcase,
  faEnvelope,
} from "@fortawesome/free-solid-svg-icons";
import { faGithub, faLinkedin } from "@fortawesome/free-brands-svg-icons";

const icons = [
  {
    data: faEnvelope,
    title: "email",
    label: "Email",
    link: "mailto:hello@zachhardesty.com"
  },
  {
    data: faGithub,
    title: "github",
    label: "Github",
    link: "https://github.com/zachhardesty7"
  },
  {
    data: faLinkedin,
    title: "linkedIn",
    label: "LinkedIn",
    link: "https://www.linkedin.com/in/zachhardesty7"
  },
  {
    data: faBriefcase,
    title: "resume",
    label: "Resume",
    link:
      "https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing"
  }
];

export default icons;