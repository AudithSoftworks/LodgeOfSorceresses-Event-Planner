export const soulshriven = cy => {
    cy.fixture('xhr-data/onboarding/soulshriven/content/step-1.json').as('onboardingSoulshrivenStep1');
    cy.route('GET', '/api/onboarding/soulshriven/content/by-step/1', '@onboardingSoulshrivenStep1');

    cy.fixture('xhr-data/onboarding/soulshriven/content/step-2.json').as('onboardingSoulshrivenStep2');
    cy.route('GET', '/api/onboarding/soulshriven/content/by-step/2', '@onboardingSoulshrivenStep2');

    cy.fixture('xhr-data/onboarding/soulshriven/content/step-3.json').as('onboardingSoulshrivenStep3');
    cy.route('GET', '/api/onboarding/soulshriven/content/by-step/3', '@onboardingSoulshrivenStep3');

    cy.fixture('xhr-data/users/soulshriven-with-no-name-and-no-forum-oauth.json').as('soulshrivenWithNoNameAndNoForumOauth');
    cy.route('POST', '/api/onboarding/finalize', '@soulshrivenWithNoNameAndNoForumOauth');
};
