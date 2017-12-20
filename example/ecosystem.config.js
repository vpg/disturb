// Example PM2 config

const workflowFilePath = "./Full/testWorkflowConfig.json";
//const workflowFilePath = "./Full/testWorkflowConfig.php";

const managerScriptFilePath = "./vendor/bin/disturb-manager";
const stepScriptFilePath = "./vendor/bin/disturb-step";

const baseArgs = `start --workflow=${workflowFilePath}`;
const interpreter = "bash";
const watch = false;

let envHash = {
    env: {
        DISTURB_ELASTIC_HOST: 'http://10.13.22.227:9200',
        DISTURB_KAFKA_BROKER: '10.13.11.27,10.13.11.28,10.13.11.29'
    },
    env_debug: {
        DISTURB_DEBUG: 1
    }
};

let workflow;

// check conf ext
if (workflowFilePath.substr(-3) === 'php') {
    // PHP
    workflow = JSON.parse(require('child_process').execSync('php ' + workflowFilePath + ' --format=json').toString());
} else {
    // JSON
    workflow = require(workflowFilePath);
}

let appList = [];
let managerHash = {
    name  : "test-manager-fr",
    script      : managerScriptFilePath,
    watch       : watch,
    args        : baseArgs,
    interpreter : interpreter
};
appList.push(Object.assign(managerHash, envHash));


workflow.steps.forEach(step => {
    if(step instanceof Array) {
    step.forEach(step => {
        addApp(workflow.name, step)
});
}
else {
    addApp(workflow.name, step)
}
});

function addApp(workflowName, step) {
    const nbInstance = step.instances ? step.instances : 1;
    for (let i=0; i<nbInstance; i++) {
        let appName = `${workflowName}-step-${step.name}-fr`;
        appName += i > 0 ? `-${i}` : '';
        let appHash = {
            name : appName,
            script : stepScriptFilePath,
            watch       : watch,
            args        : baseArgs + ` --step=${step.name} --workerId=${i}`,
            interpreter : interpreter
        };
        appList.push(Object.assign(appHash, envHash));
    }
}

let conf = {};
conf.apps = appList;

module.exports = conf;