package de.fehrprice.net;

import de.fehrprice.crypto.AES;
import de.fehrprice.crypto.Conv;
import de.fehrprice.crypto.Curve25519;
import de.fehrprice.crypto.Ed25519;

public class ECConnection {

	private Curve25519 x;
	private Ed25519 ed;
	private AES aes;

	private static String uBasePoint     = "0900000000000000000000000000000000000000000000000000000000000000";

	
	public ECConnection(Curve25519 x, Ed25519 ed, AES aes) {
		this.x = x;
		this.ed = ed;
		this.aes = aes;
	}

	/**
	 * Initiate Connection by creating a shared secret. This secret will then be used as AES session key.
	 * The keys should be new for each communication session. Generate them with Curve25519.
	 * @param clientPrivateKey
	 * @param serverPublicKey
	 * @return Shared Secret - not to be transfered!!
	 */
	public String initiateECDH(String clientPrivateKey, String serverPublicKey) {
		String sharedSecret = x.x25519(clientPrivateKey, serverPublicKey);
		return sharedSecret;
	}

	/**
	 * Create new client public key for this session. 
	 * Assemble a message with the callers id and public key. Sign the message with the static private key of the client.
	 * Append the signature to the message and call server to receive its public session key.
	 * The temporary keys should be new for each communication session. Generate them with Curve25519.
	 * The static keys for signing are created by Ed25519 and never changed.
	 * Both parties need to have the public Ed25519 key of the other party for verification. 
	 * @param clientPrivateKey
	 * @param serverPublicKey
	 * @return signed message as Json string, ready to be transmitted
	 */
	public String initiateECDSA(String staticClientPrivateKey, String staticClientPublicKey, String clientName) {
		String sessionClientPrivateKey = Conv.toString(aes.random(32));
		String sessionClientPublicKey = x.x25519(sessionClientPrivateKey, uBasePoint);
		DTO dto = new DTO();
		dto.command = "InitClient";
		dto.id = clientName;
		dto.key = sessionClientPublicKey;
		dto.signature = ed.signature(dto.getMessage(), staticClientPrivateKey, staticClientPublicKey);
		return DTO.asJson(dto);
	}

}
