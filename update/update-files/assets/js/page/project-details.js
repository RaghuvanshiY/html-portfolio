"use strict";

var config = {
  type: 'line',
  data: {
    labels: [label_todo, label_in_progress, label_review, label_done],
    datasets: [
      {
        label: label_tasks,
        backgroundColor: "rgb(255, 203, 174)",
        borderColor: "#f16c20",
        data: [todo_task, inprogress_task, review_task, done_task]
      },
    ]
  },
  options: {
    maintainAspectRatio: false,
    scales: {
      xAxes: [{ reverse: !0, gridLines: { color: "rgba(0,0,0,0.05)" } }],
      yAxes: [{
        ticks: { stepSize: 10, display: !1 },
        min: 10,
        max: 100,
        display: !0,
        borderDash: [5, 5],
        gridLines: { color: "rgba(0,0,0,0)", fontColor: "#fff" }
      }]
    },
    responsive: true,
    title: {
      display: false,
    },
    tooltips: {
      mode: 'index',
      intersect: false,
    },
    hover: {
      mode: 'nearest',
      intersect: true
    },
    legend: {
      display: false
    }
  }
};

window.onload = function () {
  var ctx = document.getElementById('task-area-chart').getContext('2d');
  window.myLine = new Chart(ctx, config);
};



Dropzone.autoDiscover = false;
var myDropzone = new Dropzone("#project-files-dropzone", {
  url: base_url + 'projects/add_project_file',
  autoProcessQueue: true,
  dictDefaultMessage: dictDefaultMessage,
  addRemoveLinks: true,
  maxFiles: 2,
  dictMaxFilesExceeded: 'Only 2 files are allow at once',
  dictRemoveFile: 'x',

});

myDropzone.on("addedfile", function (file) {
  var i = 0;
  if (this.files.length) {
    var _i, _len;
    for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) // -1 to exclude current file
    {
      if (this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
        this.removeFile(file);
      } else if (this.files[4] != null) {
        this.removeFile(file);

      }
      i++;
    }
  }
});

myDropzone.on('sending', function (file, xhr, formData) {
  console.log('data' + JSON.stringify(formData));
  formData.append('workspace_id', jQuery('#workspace_id').val());
  formData.append('project_id', jQuery('#project_id').val());
  formData.append(csrfName, csrfHash);
});

myDropzone.on("queuecomplete", function (file) {
  location.reload();
});