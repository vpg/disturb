// foo
const workflowFilePath = "./Full/test.json";

const managerScriptFilePath = "./vendor/bin/disturb-manager";
const stepScriptFilePath = "./vendor/bin/disturb-step";

const baseArgs = `start --workflow=${workflowFilePath}`;
const interpreter = "bash";
const watch = false;

let envHash = {
    env: {
        ENV_SUB: "fr"
    },
    env_debug: {
        ENV_SUB: "fr",
        DISTURB_DEBUG : 1
    }
};

let workflow = require(workflowFilePath);

let appList = [];
let managerHash = {
    name  : "test-manager-fr",
    script      : "./vendor/bin/disturb-manager",
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
        }
        appList.push(Object.assign(appHash, envHash));
    }

}

console.log(appList);

let conf = {}
conf.apps = appList;

module.exports = conf;
