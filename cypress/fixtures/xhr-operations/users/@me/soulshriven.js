export const stubSoulshrivenWithNoNameAndNoForumOauth = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-name-and-no-forum-oauth.json').as('soulshriven');
    cy.route({
        method: 'GET',
        url: '/api/users/@me',
        delay: 1000,
        response: '@soulshriven'
    }).as('loadSoulshrivenWithNoNameAndNoForumOauth');
};

export const stubSoulshrivenWithNoForumOauth = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-forum-oauth.json').as('soulshriven');
    cy.route({
        method: 'GET',
        url: '/api/users/@me',
        delay: 1000,
        response: '@soulshriven'
    }).as('loadSoulshrivenWithNoForumOauth');
};

export const stubUpdateName = cy => {
    cy.fixture('xhr-data/users/soulshriven-with-no-forum-oauth.json').as('soulshriven');
    cy.route({
        method: 'POST',
        url: '/api/users/@me',
        response: '@soulshriven',
        delay: 1000,
    }).as('updateSoulshrivenName');
};
