export const members = cy => {
    cy.fixture('xhr-data/onboarding/members/content/step-1.json').as('onboardingMembersStep1');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/members/content/by-step/1',
        delay: 2000,
        response: '@onboardingMembersStep1',
    }).as('loadOnboardingMembersStep1');

    cy.fixture('xhr-data/onboarding/members/content/step-2.json').as('onboardingMembersStep2');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/members/content/by-step/2',
        delay: 2000,
        response: '@onboardingMembersStep2',
    }).as('loadOnboardingMembersStep2');

    cy.fixture('xhr-data/onboarding/members/content/step-3.json').as('onboardingMembersStep3');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/members/content/by-step/3',
        delay: 2000,
        response: '@onboardingMembersStep3',
    }).as('loadOnboardingMembersStep3');

    cy.fixture('xhr-data/onboarding/members/content/step-4.json').as('onboardingMembersStep4');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/members/content/by-step/4',
        delay: 2000,
        response: '@onboardingMembersStep4',
    }).as('loadOnboardingMembersStep4');

    cy.fixture('xhr-data/users/member-with-no-name-and-no-forum-oauth.json').as('memberWithNoNameAndNoForumOauth');
    cy.route({
        method: 'POST',
        url: '/api/onboarding/finalize',
        delay: 1000,
        response: '@memberWithNoNameAndNoForumOauth',
    }).as('loadOnboardingMembersFinalize');
};
