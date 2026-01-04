const MAX_ALT = 42000;

document.addEventListener("DOMContentLoaded", () => {
  /*** Marquee effect ***/
  const marquees = document.getElementsByClassName("marquee");

  for (const el of marquees) {
    const children = el.childNodes;
    for (const child of [...children]) {
      const clone = child.cloneNode(true);
      el.appendChild(clone);
    }
  }

  /*** Right altitude indicator ***/

  const altPos = document.getElementById("alt-pos");
  const altIndicator = document.getElementById("alt-indicator");
  const altCenter = document.getElementById("alt-center");
  const main = document.getElementById("main");

  for (let alt = MAX_ALT; alt >= 0; alt -= 100) {
    const el = document.createElement("div");
    if (alt % 200 === 0) {
      el.innerHTML = alt.toString();
    }
    altPos.appendChild(el);
  }

  function handleAltIndicator() {
    const altTapeHeight = altIndicator.scrollHeight;

    const height = main.scrollHeight;
    const top = main.scrollTop;
    const offset = main.offsetHeight;
    const topAlt = MAX_ALT * (1 - top / height);
    const currentAlt = topAlt - (MAX_ALT / altTapeHeight) * (offset / 2);
    altCenter.innerHTML = Math.floor(currentAlt);

    const tapeScrollPos = altTapeHeight * (1 - topAlt / MAX_ALT);
    altIndicator.scroll({ top: tapeScrollPos });
  }

  handleAltIndicator();
  main.addEventListener("scroll", handleAltIndicator);
  window.addEventListener("resize", handleAltIndicator);

  /*** Heading Indicator ***/

  const radar = document.getElementById("radar");

  // Create SVG element
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("viewBox", "0 0 100 100");
  svg.setAttribute("class", "radar-svg");

  // Create rotating compass rose group
  const compassRose = document.createElementNS(
    "http://www.w3.org/2000/svg",
    "g",
  );
  compassRose.setAttribute("class", "compass-rose");
  compassRose.style.transformOrigin = "50% 50%";

  // Add cardinal and intercardinal directions
  const directions = [
    { angle: 0, label: "N", primary: true },
    { angle: 30, label: "3", primary: false },
    { angle: 60, label: "6", primary: false },
    { angle: 90, label: "E", primary: true },
    { angle: 120, label: "12", primary: false },
    { angle: 150, label: "15", primary: false },
    { angle: 180, label: "S", primary: true },
    { angle: 210, label: "21", primary: false },
    { angle: 240, label: "24", primary: false },
    { angle: 270, label: "W", primary: true },
    { angle: 300, label: "30", primary: false },
    { angle: 330, label: "33", primary: false },
  ];

  directions.forEach(({ angle, label, primary }) => {
    const x = 50 + 40 * Math.cos(((angle + 270) / 360) * 2 * Math.PI);
    const y = 50 + 40 * Math.sin(((angle + 270) / 360) * 2 * Math.PI);

    const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
    text.setAttribute("x", x);
    text.setAttribute("y", y);
    text.setAttribute(
      "class",
      primary ? "direction-marker primary" : "direction-marker",
    );
    text.setAttribute("text-anchor", "middle");
    text.setAttribute("dominant-baseline", "middle");
    text.textContent = label;
    compassRose.appendChild(text);
  });

  svg.appendChild(compassRose);

  // Create center aircraft symbol
  const aircraft = document.createElementNS(
    "http://www.w3.org/2000/svg",
    "text",
  );
  aircraft.setAttribute("x", "50");
  aircraft.setAttribute("y", "50");
  aircraft.setAttribute("class", "aircraft-symbol");
  aircraft.setAttribute("text-anchor", "middle");
  aircraft.setAttribute("dominant-baseline", "middle");
  aircraft.textContent = "▲";
  svg.appendChild(aircraft);

  radar.appendChild(svg);

  function handleHeadingIndicator() {
    const height = main.scrollHeight;
    const top = main.scrollTop;
    const scrollProgress = top / height;
    const heading = scrollProgress * 360;

    compassRose.style.transform = `rotate(${-heading}deg)`;
  }

  handleHeadingIndicator();
  main.addEventListener("scroll", handleHeadingIndicator);
  window.addEventListener("resize", handleHeadingIndicator);
});
