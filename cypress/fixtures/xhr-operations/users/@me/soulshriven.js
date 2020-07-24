export const stubSoulshrivenWithNoNameAndNoForumOauth = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-name-and-no-forum-oauth.json').as('soulshriven');
    cy.route('GET', '/api/users/@me', '@soulshriven');
};

export const stubSoulshrivenWithNoForumOauth = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-forum-oauth.json').as('soulshriven');
    cy.route('GET', '/api/users/@me', '@soulshriven');
};

export const stubUpdateName = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-forum-oauth.json').as('soulshriven');
    cy.route({
        method: 'POST',
        url: '/api/users/@me',
        response: '@soulshriven'
    }).as('updateMePostRequest');
};
