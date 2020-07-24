export const members = cy => {
    cy.fixture('xhr-data/onboarding/members/content/step-1.json').as('onboardingMembersStep1');
    cy.route('GET', '/api/onboarding/members/content/by-step/1', '@onboardingMembersStep1');

    cy.fixture('xhr-data/onboarding/members/content/step-2.json').as('onboardingMembersStep2');
    cy.route('GET', '/api/onboarding/members/content/by-step/2', '@onboardingMembersStep2');

    cy.fixture('xhr-data/onboarding/members/content/step-3.json').as('onboardingMembersStep3');
    cy.route('GET', '/api/onboarding/members/content/by-step/3', '@onboardingMembersStep3');

    cy.fixture('xhr-data/onboarding/members/content/step-4.json').as('onboardingMembersStep4');
    cy.route('GET', '/api/onboarding/members/content/by-step/4', '@onboardingMembersStep4');

    cy.fixture('xhr-data/users/member-with-no-name-and-no-forum-oauth.json').as('memberWithNoNameAndNoForumOauth');
    cy.route('POST', '/api/onboarding/finalize', '@memberWithNoNameAndNoForumOauth');
};
