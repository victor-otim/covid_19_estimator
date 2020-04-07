const fs = require('fs');
const axios = require('axios');
const xml2json = require("xml2json");
const core = require('@actions/core');
const { context } = require('@actions/github');

const CHALLENGES = {
  'ch-1': 'challenge-01',
  'ch-2': 'challenge-02',
  'ch-3': 'challenge-03',
  'ch-4': 'challenge-04',
  'ch-5': 'challenge-05'
};

const getStatsFor = (lang, task) => {
  console.log('getStatsFor: ', lang, task);
  let stats = {};

  if (lang === 'javscript' || ['ch-4', 'ch-5'].includes(task)) {
    const payload = require(`../../../audits/${task}.json`);
    stats.numTotalTests = payload.numTotalTests;
    stats.numPassedTests = payload.numPassedTests;
  }

  if (lang === 'python') {
    // JSON:: report > summary > passed | num_tests
    const data = require(`../../../audits/${task}.json`);
    const payload = data.report.summary;
    stats.numTotalTests = payload.num_tests;
    stats.numPassedTests = payload.passed;
  }

  if (lang === 'php') {
    console.log(process.cwd());
    const xml = fs.readFileSync(`${process.cwd()}/audits/${task}.xml`, 'utf8');
    const data = xml2json.toJson(xml, { object: true });
    
    // HINT: how data looks.
    // {
    //   testsuites: {
    //     testsuite: {
    //       name: './audits/ch-1',
    //       tests: '3',
    //       assertions: '36',
    //       errors: '0',
    //       warnings: '0',
    //       failures: '3',
    //       skipped: '0',
    //       time: '0.350120',
    //       testsuite: [Object]
    //     }
    //   }
    // }

    const payload = data.testsuites.testsuite;
    stats.numErrors = payload.errors;
    stats.numTotalTests = payload.tests;
    stats.numFailedTests = payload.failures;
  }

  return stats;
};

const run = async () => {
  try {
    const task = core.getInput('challenge');
    const language = core.getInput('lang');
    const challenge = CHALLENGES[task];
    const stats = getStatsFor(language, task);

    const { repo, owner } = context.repo;
    console.log('repo', context.repo);

    const report = {
      repo,
      owner,
      ...stats,
      language,
      source: 'jest',
      type: challenge
    };

    const server = core.getInput('server');
    await axios.post(`${server}/entry-tests`, { report });
  } catch (error) {
    core.setFailed(error.message);
  }
};

run();
