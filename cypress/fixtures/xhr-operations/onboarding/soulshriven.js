export const soulshriven = cy => {
    cy.fixture('xhr-data/onboarding/soulshriven/content/step-1.json').as('onboardingSoulshrivenStep1');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/soulshriven/content/by-step/1',
        delay: 1000,
        response: '@onboardingSoulshrivenStep1'
    }).as('loadOnboardingSoulshrivenStep1');

    cy.fixture('xhr-data/onboarding/soulshriven/content/step-2.json').as('onboardingSoulshrivenStep2');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/soulshriven/content/by-step/2',
        delay: 1000,
        response: '@onboardingSoulshrivenStep2'
    }).as('loadOnboardingSoulshrivenStep2');

    cy.fixture('xhr-data/onboarding/soulshriven/content/step-3.json').as('onboardingSoulshrivenStep3');
    cy.route('GET', '/api/onboarding/soulshriven/content/by-step/3', '@onboardingSoulshrivenStep3');
    cy.route({
        method: 'GET',
        url: '/api/onboarding/soulshriven/content/by-step/3',
        delay: 1000,
        response: '@onboardingSoulshrivenStep3'
    }).as('loadOnboardingSoulshrivenStep3');

    cy.fixture('xhr-data/users/soulshriven-with-no-name-and-no-forum-oauth.json').as('soulshrivenWithNoNameAndNoForumOauth');
    cy.route({
        method: 'POST',
        url: '/api/onboarding/finalize',
        delay: 1000,
        response: '@soulshrivenWithNoNameAndNoForumOauth'
    }).as('loadOnboardingSoulshrivenFinalize');
};
