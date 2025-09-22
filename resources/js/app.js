import './bootstrap';

// flowbite
import 'flowbite';

// === CSS bawaan template ===
import "jsvectormap/dist/jsvectormap.min.css";
import "flatpickr/dist/flatpickr.min.css";
import "dropzone/dist/dropzone.css";
import "../css/custom.css";

// === Alpine ===
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
Alpine.plugin(persist);

window.Alpine = Alpine;
Alpine.start();

// === Flatpickr & Dropzone ===
import flatpickr from "flatpickr";
import Dropzone from "dropzone";

// === ApexCharts & FullCalendar ===
import ApexCharts from "apexcharts";
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import listPlugin from "@fullcalendar/list";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
window.ApexCharts = ApexCharts;
window.FullCalendar = { Calendar, dayGridPlugin, listPlugin, timeGridPlugin, interactionPlugin };

// === Komponen custom ===
import chart01 from "./components/charts/chart-01.js";
import chart02 from "./components/charts/chart-02.js";
import chart03 from "./components/charts/chart-03.js";
import map01 from "./components/map-01.js";
import "./components/calendar-init.js";
import "./components/image-resize.js";

// === Init flatpickr ===
flatpickr(".datepicker", {
  mode: "range",
  static: true,
  monthSelectorType: "static",
  dateFormat: "M j, Y",
  defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
  prevArrow: '...',
  nextArrow: '...',
  onReady: (selectedDates, dateStr, instance) => {
    instance.element.value = dateStr.replace("to", "-");
    const customClass = instance.element.getAttribute("data-class");
    instance.calendarContainer.classList.add(customClass);
  },
  onChange: (selectedDates, dateStr, instance) => {
    instance.element.value = dateStr.replace("to", "-");
  },
});

// === Init Dropzone ===
const dropzoneArea = document.querySelectorAll("#demo-upload");
if (dropzoneArea.length) {
  let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

// === Jalankan chart/map ===
document.addEventListener("DOMContentLoaded", () => {
  chart01();
  chart02();
  chart03();
  map01();
});

// === Tahun otomatis ===
const year = document.getElementById("year");
if (year) {
  year.textContent = new Date().getFullYear();
}
