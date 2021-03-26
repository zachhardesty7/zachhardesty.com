export default [
  {
    title: "My Personal Portfolio Website",
    description:
      "This is the website you're looking at right now! It's built with Svelte to allow projects to be easily added, removed, and updated by just modifying a simple JSON data file. Svelte enables reactivity without the overhead of a virtual DOM by running entirely at compile time. Sapper renders the final HTML server-side (SSR) before serving it via Netlify. This works very similar to the React SSR library previously used for this website, Gatsby.",
    image: "website.png",
    link: "https://zachhardesty.com",
    github: "https://github.com/zachhardesty7/zachhardesty.com",
    skills: [
      "Svelte",
      "Sapper",
      "React",
      "Gatsby",
      "JavaScript",
      "CSS Grid",
      "HTML5",
      "Webpack",
    ],
    case: {
      overview: [],
      context: [],
      challenge: [],
      objectives: [],
      process: [],
      insight: [],
      solution: [],
      results: [],
    },
  },
  {
    title: "Missive GCal Export Widget",
    description:
      "Adds an integration to the Missive mail app that parses emails for dates/times and displays them to export to Google Calendar if desired. Clicking the export button will automatically open Google Calendar's \"New Event\" page and allow you to customize fields' content.",
    image: "missive.jpg",
    link: "https://gcal.netlify.com/",
    github: "https://github.com/zachhardesty7/missive-gcal-export-integration",
    skills: ["JavaScript", "React", "Missive", "Email", "Google Calendar"],
  },
  {
    title: "Phinder",
    description:
      "Mobile app proof-of-concept that helps organizations and students on campus connect with each other. Uses React Native and Expo to accelerate the app development process.",
    image: "phinder.jpg",
    link:
      "https://expo.io/appetize-simulator?url=https://expo.io/@zachhardesty7/phinder",
    github: "https://github.com/zachhardesty7/phinder",
    skills: [
      "JavaScript",
      "React",
      "React Native",
      "Expo",
      "Android",
      "Apple",
      "Mobile",
      "App",
    ],
  },
  {
    title: "Semantic Styled UI",
    description:
      "A UI component library for React that styles content based on Semantic UI but improves the default styles with Styled Components. It allows for easy plug and play in projects looking to take advantage of Styled Components without dealing with the hassle of integrating Semantic UI styles manually. It also includes some useful components and prebuilt components for rapid web app development.",
    link: "",
    github: "https://github.com/zachhardesty7/semantic-styled-ui",
    skills: [
      "JavaScript",
      "React",
      "SemanticUI",
      "Style Components",
      "CSS-in-JS",
    ],
  },
  {
    title: "Tampermonkey / Greasemonkey Scripts Collection",
    description:
      "This repo hosts a handful of different script files that Tampermonkey injects into the relevant websites. Certain websites just seemed to be missing something or something simple could really improve the functionality. I took it upon myself to add these features in the most seamless way possible. Uses Standard JS formatting.",
    link: "https://openuserjs.org/users/zachhardesty7/scripts",
    github: "https://github.com/zachhardesty7/tamper-monkey-scripts-collection",
    skills: [
      "JavaScript",
      "Greasemonkey",
      "Tampermonkey",
      "User Script",
      "Content Script",
      "Extension",
    ],
  },
  {
    title: "GroupMe Notifier",
    description:
      "Uses a little python script hosted on Heroku to poll a given GroupMe group chat for keywords and emails a summary to you. Can be handy for giant groups that often have chatter interspersed between important notifications.",
    link: "",
    github: "https://github.com/zachhardesty7/group-me-notifier",
    skills: ["Python", "Heroku", "GroupMe API"],
  },
];
