//const workflowFilePath = "./Full/test.json";
const workflowFilePath = "./Full/test.php";

const managerScriptFilePath = "./vendor/bin/disturb-manager";
const stepScriptFilePath = "./vendor/bin/disturb-step";

const baseArgs = `start --workflow=${workflowFilePath}`;
const interpreter = "bash";
const watch = false;


// display disturb env var
console.info('====================');
console.info('= DISTURB ENV VARS =');
console.info('====================');
let processEnvHash = process.env;
Object.keys(processEnvHash).forEach(function (name) {
    if (name.substring(0, 7) === 'DISTURB') {
        console.info(name + ':' + processEnvHash[name]);
    }
});
console.info('');

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
appList.push(managerHash);

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
        appList.push(appHash);
    }
}

let conf = {};
conf.apps = appList;

module.exports = conf;
